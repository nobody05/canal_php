<?php


namespace PhpOne\CanalPHP\Connector\impl;

use Com\Alibaba\Otter\Canal\Protocol\Ack;
use Com\Alibaba\Otter\Canal\Protocol\Messages;
use Com\Alibaba\Otter\Canal\Protocol\PacketType;
use Google\Protobuf\Internal\Message;
use PhpOne\CanalPHP\ClientIdentity;
use PhpOne\CanalPHP\Constants;
use PhpOne\CanalPHP\PacketUtil;
use \Swoole\Client;
use PhpOne\CanalPHP\Connector\Connector;
use Symfony\Component\String\ByteString;

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


    public function __construct(string $address, string $port, string $destination, string $username, String $password)
    {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->address = $address;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->destination = $destination;

        $this->clientIdentity = new ClientIdentity($destination, Constants::DEFAULT_CLIENT_ID);

        $this->_setClientConfig();

        register_shutdown_function([$this, 'disconnect']);
    }

    public function connect()
    {
        $connected = $this->client->connect($this->address, $this->port);
        if (!$connected) {
            throw new \Exception("connect server error: ". $this->client->errCode);
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

    public function getWithoutAck(int $batchSize)
    {
        echo "getWithoutAck msg". PHP_EOL;

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

    private function receiveMessages()
    {
        echo "receive msg". PHP_EOL;
        $receivePacket = $this->body2Packet($this->readNextPacket());

        $message = new \PhpOne\CanalPHP\Message();
        switch ($receivePacket->getType()) {
            case PacketType::MESSAGES:

                echo "new-message". PHP_EOL;

                $messages = $this->body2Messages($receivePacket->getBody());

                print_r($messages->getBatchId());
                echo "batchId". PHP_EOL;

                if ($messages->getBatchId() > 0) {
                    $message->setId($messages->getBatchId());

                    foreach ($messages->getMessages()->getIterator() as $messageBody) {
//                        $byteString = $this->string2ByteString($messageBody)
//                            ->toByteString()->toString();
                        $entry = $this->body2Entry($messageBody);
                        $message->addEntry($entry);
                    }

                    print_r($message->count());
                    echo "messsageCount". PHP_EOL;

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

    public function get()
    {

    }

    protected function doConnect()
    {
        // handshake
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

        echo "send auth ". PHP_EOL;

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

    public function disconnect()
    {
        @$this->client->close();
    }

    public function unsubscribe()
    {

    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    public function bodyWithHeader($body)
    {
        return pack("N", strlen($body)). $body;
    }
}