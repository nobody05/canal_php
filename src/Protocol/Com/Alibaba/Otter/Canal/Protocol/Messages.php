<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: CanalProtocol.proto

namespace Com\Alibaba\Otter\Canal\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *
 * Generated from protobuf message <code>com.alibaba.otter.canal.protocol.Messages</code>
 */
class Messages extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int64 batch_id = 1;</code>
     */
    private $batch_id = 0;
    /**
     * Generated from protobuf field <code>repeated bytes messages = 2;</code>
     */
    private $messages;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $batch_id
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $messages
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\CanalProtocol::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int64 batch_id = 1;</code>
     * @return int|string
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }

    /**
     * Generated from protobuf field <code>int64 batch_id = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setBatchId($var)
    {
        GPBUtil::checkInt64($var);
        $this->batch_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated bytes messages = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Generated from protobuf field <code>repeated bytes messages = 2;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setMessages($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::BYTES);
        $this->messages = $arr;

        return $this;
    }

}

