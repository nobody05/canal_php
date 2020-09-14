<?php


namespace PhpOne\CanalPHP;


use Com\Alibaba\Otter\Canal\Protocol\Ack;
use Com\Alibaba\Otter\Canal\Protocol\ClientAck;
use Com\Alibaba\Otter\Canal\Protocol\ClientAuth;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\Get;
use Com\Alibaba\Otter\Canal\Protocol\Handshake;
use Com\Alibaba\Otter\Canal\Protocol\Messages;
use Com\Alibaba\Otter\Canal\Protocol\Packet;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\Sub;
use Symfony\Component\String\ByteString;
use \Google\Protobuf\Internal\Message;

trait PacketUtil
{
    public function echo()
    {
        echo "111";
    }

    /**
     * @param $body
     * @return Packet
     * @throws \Exception
     */
    public function body2Packet($body): Packet
    {
        $packet = new Packet();
        $packet->mergeFromString($body);
        return $packet;
    }

    public function newPacket(int $packetType): Packet
    {
        $packet = new Packet();
        $packet->setType($packetType);

        return $packet;
    }

    public function body2HandShake($body): Handshake
    {
        $handShake = new Handshake();
        $handShake->mergeFromString($body);
        return $handShake;
    }

    public function newClientAuth(): ClientAuth
    {
        $clientAuth = new ClientAuth();
        return $clientAuth;
    }

    public function body2Ack($body) :Ack
    {
        $ack = new Ack();
        $ack->mergeFromString($body);
        return $ack;
    }

    public function newSub(): Sub
    {
        $sub = new Sub();
        return $sub;
    }

    public function newGet(): Get
    {
        $get = new Get();
        return $get;
    }

    public function body2Messages($body): Messages
    {
        $messages = new Messages();
        $messages->mergeFromString($body);

        return $messages;
    }

    public function newMessages(): Messages
    {
        $messages = new Messages();
        return $messages;
    }
    public function string2ByteString($strings)
    {
        $byteString = new ByteString($strings);
        return $byteString;
    }

    public function newByteString(): ByteString
    {
        $byteString = new ByteString();
        return $byteString;
    }

    public function body2Entry($body): Entry
    {
        $entry = new Entry();
        $entry->mergeFromString($body);
        return $entry;

    }

    public function newRowChange(): RowChange
    {
        $rowChange = new RowChange();
        return $rowChange;
    }

    public function value2RowChange($value) :RowChange
    {
        $rowChange = new RowChange();
        $rowChange->mergeFromString($value);

        return $rowChange;
    }

    public function newClientAck(): ClientAck
    {
        $clientAck = new ClientAck();
        return $clientAck;
    }




}