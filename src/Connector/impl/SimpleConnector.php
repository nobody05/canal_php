<?php


namespace PhpOne\CanalPHP\Connector\impl;

use Com\Alibaba\Otter\Canal\Protocol\PacketType;
use PhpOne\CanalPHP\ClientIdentity;
use PhpOne\CanalPHP\Config;
use PhpOne\CanalPHP\Exception\CanalClientException;
use PhpOne\CanalPHP\PacketUtil;
use \Swoole\Client as SClient;
use PhpOne\CanalPHP\Connector\Connector;
use \PhpOne\CanalPHP\Message;
use Swoole\Coroutine;
use \Swoole\Coroutine\Client;

/**
 * Class SimpleConnector
 * @package PhpOne\CanalPHP\Connector\impl
 */
class SimpleConnector implements Connector
{
    use PacketUtil;

    protected $client;
    protected $address;
    protected $port;
    protected $username;
    protected $password;
    protected $destination;
    protected $clientIdentity;
    protected $connected;

    /**
     * SimpleConnector constructor.
     * @param string $address
     * @param int $port
     * @param string $destination
     * @param string $username
     * @param String $password
     */
    public function __construct(string $address, int $port, string $destination, string $username = "", String $password = "")
    {
        if (Config::get("server.openCoroutine")) {
            Coroutine::set(['hook_flags'=> Config::get("server.coHookFlags")]);

            $this->client = new Client(SWOOLE_SOCK_TCP);
        } else {
            $this->client = new SClient(SWOOLE_SOCK_TCP);
        }

        $this->address = $address;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->destination = $destination;

        $this->clientIdentity = new ClientIdentity($destination, Config::get("canal.server.clientId"));

        $this->_setClientConfig();

        register_shutdown_function([$this, 'disconnect']);
    }

    /**
     * @throws CanalClientException
     */
    public function connect()
    {
        $connected = $this->client->connect($this->address, $this->port);
        if (!$connected) {
            throw new CanalClientException("connect server error: ". swoole_last_error(), $this->client->errCode);
        }

        $this->doConnect();
    }

    private function _setClientConfig()
    {
        $this->client->set(Config::get("canal.client"));
    }

    /**
     * @param string $filter
     * @throws \Exception
     */
    public function subscribe(string $filter)
    {
        $sub = $this->newSub()
            ->setClientId($this->clientIdentity->getClientId())
            ->setDestination($this->clientIdentity->getDestination())
            ->setFilter($filter);

        $packet = $this->newPacket(PacketType::SUBSCRIPTION)
            ->setBody($sub->serializeToString());

        $this->client->send($this->bodyWithHeader($packet->serializeToString()));

        $receivePacket = $this->body2Packet($this->readNextPacket());
        $ack = $this->body2Ack($receivePacket->getBody());
        if ($ack->getErrorCode()) {
            throw new \Exception("subscribe error ". $ack->getErrorMessage(), $ack->getErrorCode());
        }

        $this->clientIdentity->setFilter($filter);
    }

    /**
     * 去除包头，获取包体
     * @return bool|string
     */
    public function readNextPacket()
    {
        $data = $this->client->recv();
        $bodyLen = unpack("N", $data);
        $body = substr($data, 4, reset($bodyLen));

        return $body;
    }

    public function checkValid()
    {

    }

    /**
     * @param int $batchSize
     * @return \PhpOne\CanalPHP\Message
     * @throws \Exception
     */
    public function getWithoutAck(int $batchSize)
    {
        $get = $this->newGet()
            ->setDestination($this->clientIdentity->getDestination())
            ->setClientId($this->clientIdentity->getClientId())
            ->setFetchSize($batchSize)
            ->setAutoAck(false);

        $packet = $this->newPacket(PacketType::GET)
            ->setBody($get->serializeToString());

        $this->client->send($this->bodyWithHeader($packet->serializeToString()));

        return $this->receiveMessages();
    }

    /**
     * @return \PhpOne\CanalPHP\Message
     * @throws \Exception
     */
    private function receiveMessages()
    {
        $receivePacket = $this->body2Packet($this->readNextPacket());
        $message = new Message();

        switch ($receivePacket->getType()) {
            case PacketType::MESSAGES:
                $messages = $this->body2Messages($receivePacket->getBody());
                if ($messages->getBatchId() > 0) {
                    $message->setId($messages->getBatchId());

                    foreach ($messages->getMessages()->getIterator() as $messageBody) {
                        $entry = $this->body2Entry($messageBody);
                        $message->addEntry($entry);
                    }

                    return $message;
                }

                break;
            case PacketType::ACK:
                $ack = $this->body2Ack($receivePacket->getBody());
                if ($ack->getErrorCode()) throw new CanalClientException("receive message err ". $ack->getErrorMessage(), $ack->getErrorCode());
                break;
            default:
                throw new CanalClientException("receive message got unknow packetType ");
        }

        return $message;

    }

    /**
     * @param int $batchSize
     * @return \PhpOne\CanalPHP\Message
     * @throws \Exception
     */
    public function get(int $batchSize)
    {
        $messages = $this->getWithoutAck($batchSize);
        $this->ack($messages->getId());

        return $messages;
    }

    /**
     * @throws \Exception
     */
    protected function doConnect()
    {
        $packet = $this->body2Packet($this->readNextPacket());
        if ($packet->getType() != PacketType::HANDSHAKE) {
            throw new CanalClientException("packet type err value: ". $packet->getType());
        }

        // @TODO
        $handShake = $this->body2HandShake($packet->getBody());


        // client auth
        $clientAuth = $this->newClientAuth()
            ->setClientId($this->clientIdentity->getClientId())
            ->setDestination($this->clientIdentity->getDestination())
            ->setUsername($this->username)
            ->setPassword($this->password);
        $sendPacket = $this->newPacket(PacketType::CLIENTAUTHENTICATION)
            ->setBody($clientAuth->serializeToString());
        $this->client->send($this->bodyWithHeader($sendPacket->serializeToString()));

        // ack
        $packet = $this->body2Packet($this->readNextPacket());
        if ($packet->getType() != PacketType::ACK) {
            throw new CanalClientException("packet type err value: ". $packet->getType());
        }
        $ack = $this->body2Ack($packet->getBody());
        if ($ack->getErrorCode()) {
            throw new CanalClientException("server Ack error ". $ack->getErrorMessage(), $ack->getErrorCode());
        }

        $this->connected = true;
    }

    public function ack($batchId)
    {
        $clientAck = $this->newClientAck()
            ->setClientId($this->clientIdentity->getClientId())
            ->setDestination($this->clientIdentity->getDestination())
            ->setBatchId($batchId);

        $packet = $this->newPacket(PacketType::CLIENTACK)
            ->setBody($clientAck->serializeToString());

        $this->client->send($this->bodyWithHeader($packet->serializeToString()));

    }

    /**
     *
     */
    public function disconnect()
    {
        @$this->client->close();
    }

    /**
     *
     */
    public function unsubscribe()
    {
        $sub = $this->newSub()
            ->setClientId($this->clientIdentity->getClientId())
            ->setDestination($this->clientIdentity->getDestination())
            ->setFilter($this->clientIdentity->getFilter());

        $packet = $this->newPacket(PacketType::UNSUBSCRIPTION)
            ->setBody($sub->serializeToString());

        $this->client->send($this->bodyWithHeader($packet));
    }

    /**
     * @param int $batchId
     */
    public function rollback($batchId = 0)
    {
        $clientRollback = $this->newClientRollback()
            ->setBatchId($batchId)
            ->setDestination($this->clientIdentity->getDestination())
            ->setClientId($this->clientIdentity->getClientId());
        $packet = $this->newPacket(PacketType::CLIENTROLLBACK)
            ->setBody($clientRollback->serializeToString());

        $this->client->send($this->bodyWithHeader($packet->serializeToString()));
    }

    /**
     * @param $body
     * @return string
     */
    public function bodyWithHeader($body)
    {
        return pack("N", strlen($body)). $body;
    }
}