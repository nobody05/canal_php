<?php


namespace PhpOne\CanalPHP;


use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;

/**
 * Class Message
 * @package PhpOne\CanalPHP
 */
class Message
{
    use PacketUtil;

    protected $id = 0;
    protected $entries;

    public function __construct()
    {
        $this->entries = new \ArrayIterator([]);
    }

    public function count()
    {
        return $this->entries->count();
    }

    public function addEntry($entry)
    {
        $this->entries->append($entry);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * 把protocol对象返回
     * @return mixed
     * @throws
     */
    public function getEntries()
    {
        $result = [];
        foreach ($this->entries as $entry) {
            /**@var Entry $entry */
            $entryType = $entry->getEntryType();
            if ($entryType == EntryType::TRANSACTIONBEGIN || $entryType == EntryType::TRANSACTIONEND) {
                continue;
            }
            $rowChange = $this->value2RowChange($entry->getStoreValue());

            $result[] = [
                'tableName' => $entry->getHeader()->getTableName(),
                'eventType' => $rowChange->getEventType(),
                'eventTypeName' => EventType::name($rowChange->getEventType()),
                'rowDatas' => $rowChange->getRowDatas(),
                'entry' => $entry,
                'rowChange' => $rowChange,
            ];
        }

        return $result;
    }

    public function getEntriesOri()
    {
        return $this->entries;
    }

    /**
     * @param mixed $entries
     */
    public function setEntries($entries): void
    {
        $this->entries = $entries;
    }


}