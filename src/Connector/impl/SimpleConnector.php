<?php


namespace PhpOne\CanalPHP\Connector\impl;

use Com\Alibaba\Otter\Canal\Protocol\Ack;
use Com\Alibaba\Otter\Canal\Protocol\Messages;
use Com\Alibaba\Otter\Canal\Protocol\PacketType;
use Google\Protobuf\Internal\Message;
use PhpOne\CanalPHP\ClientIdentity;
use PhpOne\CanalPHP\Config;
use PhpOne\CanalPHP\Constants;
use PhpOne\CanalPHP\PacketUtil;
use \Swoole\Client;
use PhpOne\CanalPHP\Connector\Connector;
use Symfony\Component\String\ByteString;

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
    public function __construct(string $address, int $port, string $destination, string $username, String $password)
    {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->address = $address;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->destination = $destination;

        $this->clientIdentity = new ClientIdentity($destination, Config::get("canal.server.clientId"));

        $this->_setClientConfig();

        register_shutdown_function([$this, 'disconnect']);
    }

    public function connect()
    {
        $connected = $this->client->connect($this->address, $this->port);
        if (!$connected) {
            throw new \Exception("connect server error: ". $this->client->errCode. " msg: ");
        }

        $this->doConnect();
    }

    private function _setClientConfig()
    {
        $this->client->set([
            'open_length_check' => 1, // 是否开启包长度检测
            "package_length_offset" => 0, // 从哪个字节开始算包长，如果总长度包含包头，就从0开始
            "package_body_offset" => Constants::TCP_HEADER_LEN, // 哪个字节的值代表包体长度
            'package_length_type' => 'N' // 解包方式  N 代表32位  参考pack unpack函数
        ]);
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
        echo "getWithoutAck msg". $batchSize. PHP_EOL;

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
        $message = new \PhpOne\CanalPHP\Message();

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
                if ($ack->getErrorCode()) throw new \Exception("receive message err ". $ack->getErrorMessage(), $ack->getErrorCode());
                break;
            default:
                throw new \Exception("receive message got unknow packetType ");
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

    protected function doConnect()
    {
        $packet = $this->body2Packet($this->readNextPacket());
        if ($packet->getType() != PacketType::HANDSHAKE) {
            throw new \Exception("packet type err value: ". $packet->getType());
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
            throw new \Exception("packet type err value: ". $packet->getType());
        }
        $ack = $this->body2Ack($packet->getBody());
        if ($ack->getErrorCode()) {
            throw new \Exception("server Ack error ". $ack->getErrorMessage(), $ack->getErrorCode());
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