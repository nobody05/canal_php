<?php


namespace PhpOne\CanalPHP;


use Com\Alibaba\Otter\Canal\Protocol\Ack;
use Com\Alibaba\Otter\Canal\Protocol\ClientAck;
use Com\Alibaba\Otter\Canal\Protocol\ClientAuth;
use Com\Alibaba\Otter\Canal\Protocol\ClientRollback;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\Get;
use Com\Alibaba\Otter\Canal\Protocol\Handshake;
use Com\Alibaba\Otter\Canal\Protocol\Messages;
use Com\Alibaba\Otter\Canal\Protocol\Packet;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\Sub;
use Com\Alibaba\Otter\Canal\Protocol\Unsub;
use Symfony\Component\String\ByteString;

/**
 * Trait PacketUtil
 * @package PhpOne\CanalPHP
 */
trait PacketUtil
{
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

    /**
     * @param int $packetType
     * @return Packet
     */
    public function newPacket(int $packetType): Packet
    {
        $packet = new Packet();
        $packet->setType($packetType);

        return $packet;
    }

    /**
     * @param $body
     * @return Handshake
     * @throws \Exception
     */
    public function body2HandShake($body): Handshake
    {
        $handShake = new Handshake();
        $handShake->mergeFromString($body);
        return $handShake;
    }

    /**
     * @return ClientAuth
     */
    public function newClientAuth(): ClientAuth
    {
        $clientAuth = new ClientAuth();
        return $clientAuth;
    }

    /**
     * @param $body
     * @return Ack
     * @throws \Exception
     */
    public function body2Ack($body): Ack
    {
        $ack = new Ack();
        $ack->mergeFromString($body);
        return $ack;
    }

    /**
     * @return Sub
     */
    public function newSub(): Sub
    {
        $sub = new Sub();
        return $sub;
    }

    /**
     * @return Get
     */
    public function newGet(): Get
    {
        $get = new Get();
        return $get;
    }

    /**
     * @param $body
     * @return Messages
     * @throws \Exception
     */
    public function body2Messages($body): Messages
    {
        $messages = new Messages();
        $messages->mergeFromString($body);

        return $messages;
    }

    /**
     * @return Messages
     */
    public function newMessages(): Messages
    {
        $messages = new Messages();
        return $messages;
    }

    /**
     * @param $strings
     * @return ByteString
     */
    public function string2ByteString($strings)
    {
        $byteString = new ByteString($strings);
        return $byteString;
    }

    /**
     * @return ByteString
     */
    public function newByteString(): ByteString
    {
        $byteString = new ByteString();
        return $byteString;
    }

    /**
     * @param $body
     * @return Entry
     * @throws \Exception
     */
    public function body2Entry($body): Entry
    {
        $entry = new Entry();
        $entry->mergeFromString($body);
        return $entry;

    }

    /**
     * @return RowChange
     */
    public function newRowChange(): RowChange
    {
        $rowChange = new RowChange();
        return $rowChange;
    }

    /**
     * @param $value
     * @return RowChange
     * @throws \Exception
     */
    public function value2RowChange($value): RowChange
    {
        $rowChange = new RowChange();
        $rowChange->mergeFromString($value);

        return $rowChange;
    }

    /**
     * @return ClientAck
     */
    public function newClientAck(): ClientAck
    {
        $clientAck = new ClientAck();
        return $clientAck;
    }

    /**
     * @return ClientRollback
     */
    public function newClientRollback(): ClientRollback
    {
        $clientRollBack = new ClientRollback();
        return $clientRollBack;
    }

    public function newUnsubscrib(): Unsub
    {
        $unSub = new Unsub();
        return $unSub;
    }



}