<?php


namespace PhpOne\CanalPHP;


use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use PhpOne\CanalPHP\Exception\CanalClientException;

class CanalClient
{
    use PacketUtil;

    /**
     * @throws \Exception
     */
    public function start()
    {
        $connector = ConnectorFactory::getSimpleConnector(
            Config::get("canal.server.address"), Config::get("canal.server.port"), Config::get("canal.server.destination"),
            (string) Config::get("canal.server.username"), (string) Config::get("canal.server.password"));
        try {
            $connector->connect();
            // 订阅表
            $connector->subscribe(Config::get("canal.server.filter"));
            $connector->rollback();

            $currentEmpty = 0;
            while ($currentEmpty < Config::get("canal.maxWhileCount")) {
                $message = $connector->getWithoutAck(Config::get("canal.batchSize"));
                $batchId = $message->getId();
                if ($batchId > 0) {
                    $currentEmpty = 0;

                    if (Config::get("canal.messageCallback")) {
                        [$class, $func] = Config::get("canal.messageCallback");
                        if (class_exists($class)) {
                            call_user_func_array([$class, $func], [$message->getEntries()]);
                        }
                    }

                    if (Config::get("canal.openMessagePrint")) {
                        $this->printEntry($message->getEntriesOri());
                    }

                    $connector->ack($batchId);
                } else {

                    echo "no-message". PHP_EOL;

                    $currentEmpty++;

                    sleep(1);
                }
            }
        } catch (\Exception $e) {
            throw new CanalClientException($e);
        } finally {
            $connector->disconnect();
        }

    }

    /**
     * @param $entries
     * @throws \Exception
     */
    public function printEntry($entries)
    {
        foreach ($entries as $entry) {
            /**@var Entry $entry */
            $entryType = $entry->getEntryType();

            if ($entryType == EntryType::TRANSACTIONBEGIN || $entryType == EntryType::TRANSACTIONEND) {
                continue;
            }

            $rowChange = $this->value2RowChange($entry->getStoreValue());
            $eventType = $rowChange->getEventType();


            echo sprintf("eventType: %s", $eventType). PHP_EOL;

            foreach ($rowChange->getRowDatas() as $rowData) {
                /**@var RowData $rowData*/
                if ($eventType == EventType::INSERT) {
                    echo "Insert Record". PHP_EOL;
                    $this->printColumn($rowData->getAfterColumns());
                } elseif ($eventType == EventType::DELETE) {
                    echo "Delete Record". PHP_EOL;
                    $this->printColumn($rowData->getBeforeColumns());
                } elseif ($eventType == EventType::UPDATE) {
                    echo "update column". PHP_EOL;

                    echo "before update". PHP_EOL;
                    $this->printColumn($rowData->getBeforeColumns());

                    echo "after update". PHP_EOL;
                    $this->printColumn($rowData->getAfterColumns());

                } else {

                    echo "other change". PHP_EOL;
                }

            }



        }

    }

    /**
     * @param $columns
     */
    public function printColumn($columns)
    {
        /**@var Column $column*/
        foreach ($columns as $column) {
            echo sprintf("column : %s, value: %s, isUpdated: %d", $column->getName(), $column->getValue(), $column->getUpdated()). PHP_EOL;
        }

    }
}