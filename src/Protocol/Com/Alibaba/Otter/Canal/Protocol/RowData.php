<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: EntryProtocol.proto

namespace Com\Alibaba\Otter\Canal\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>com.alibaba.otter.canal.protocol.RowData</code>
 */
class RowData extends \Google\Protobuf\Internal\Message
{
    /**
     ** 字段信息，增量数据(修改前,删除前) *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column beforeColumns = 1;</code>
     */
    private $beforeColumns;
    /**
     ** 字段信息，增量数据(修改后,新增后)  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column afterColumns = 2;</code>
     */
    private $afterColumns;
    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 3;</code>
     */
    private $props;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Com\Alibaba\Otter\Canal\Protocol\Column[]|\Google\Protobuf\Internal\RepeatedField $beforeColumns
     *          * 字段信息，增量数据(修改前,删除前) *
     *     @type \Com\Alibaba\Otter\Canal\Protocol\Column[]|\Google\Protobuf\Internal\RepeatedField $afterColumns
     *          * 字段信息，增量数据(修改后,新增后)  *
     *     @type \Com\Alibaba\Otter\Canal\Protocol\Pair[]|\Google\Protobuf\Internal\RepeatedField $props
     *          *预留扩展*
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\EntryProtocol::initOnce();
        parent::__construct($data);
    }

    /**
     ** 字段信息，增量数据(修改前,删除前) *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column beforeColumns = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getBeforeColumns()
    {
        return $this->beforeColumns;
    }

    /**
     ** 字段信息，增量数据(修改前,删除前) *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column beforeColumns = 1;</code>
     * @param \Com\Alibaba\Otter\Canal\Protocol\Column[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setBeforeColumns($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Com\Alibaba\Otter\Canal\Protocol\Column::class);
        $this->beforeColumns = $arr;

        return $this;
    }

    /**
     ** 字段信息，增量数据(修改后,新增后)  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column afterColumns = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAfterColumns()
    {
        return $this->afterColumns;
    }

    /**
     ** 字段信息，增量数据(修改后,新增后)  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Column afterColumns = 2;</code>
     * @param \Com\Alibaba\Otter\Canal\Protocol\Column[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAfterColumns($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Com\Alibaba\Otter\Canal\Protocol\Column::class);
        $this->afterColumns = $arr;

        return $this;
    }

    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 3;</code>
     * @param \Com\Alibaba\Otter\Canal\Protocol\Pair[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setProps($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Com\Alibaba\Otter\Canal\Protocol\Pair::class);
        $this->props = $arr;

        return $this;
    }

}
