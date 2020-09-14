<?php


namespace PhpOne\CanalPHP;


use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowData;

class CanalClient
{
    use PacketUtil;

    public function start()
    {
        $this->echo();

        $connector = ConnectorFactory::getSimpleConnector("127.0.0.1", "11111", "example");
        try {
            $connector->connect();
            $connector->subscribe("test.student");

            $emptyTotal = 100;
            $currentEmpty = 0;

            while ($currentEmpty < $emptyTotal) {
                echo "while". PHP_EOL;
                $message = $connector->getWithoutAck(100);
                $batchId = $message->getId();
                if ($batchId > 0) {
                    echo "while-newmsg". PHP_EOL;

                    $currentEmpty = 0;

                    $this->printEntry($message->getEntries());




                } else {

                    echo "while-else". PHP_EOL;

                    $currentEmpty++;

                    sleep(1);
                }
//                $connector->ack($batchId);


            }
        } catch (\Exception $e) {
            echo "err". $e->getMessage(). PHP_EOL;
        } finally {
            $connector->disconnect();
        }

    }

    public function printEntry($entries)
    {

        foreach ($entries as $entry) {
            /**@var Entry $entry */
            $entryType = $entry->getEntryType();
            print_r($entryType);
            echo "entryType". PHP_EOL;

            if ($entryType == EntryType::TRANSACTIONBEGIN || $entryType == EntryType::TRANSACTIONEND) {
                continue;
            }

            $rowChange = $this->value2RowChange($entry->getStoreValue());
            $eventType = $rowChange->getEventType();

            echo sprintf("eventType: %s", $eventType). PHP_EOL;


            foreach ($rowChange->getRowDatas() as $rowData) {
                /**@var RowData $rowData*/
                if ($eventType == EventType::INSERT) {
                    echo "Insert new Record". PHP_EOL;
                    $this->printColumn($rowData->getAfterColumns());
                } elseif ($eventType == EventType::DELETE) {
                    echo "Delete Record". PHP_EOL;
                    $this->printColumn($rowData->getBeforeColumns());
                } elseif ($eventType == EventType::UPDATE) {
                    echo "update column". PHP_EOL;

                    echo "before update data". PHP_EOL;
                    $this->printColumn($rowData->getBeforeColumns());

                    echo "after update data". PHP_EOL;
                    $this->printColumn($rowData->getAfterColumns());

                } else {

                    echo "other change". PHP_EOL;
                }

            }



        }

    }

    public function printColumn($columns)
    {
        /**@var Column $column*/
        foreach ($columns as $column) {
            echo sprintf("Column name : %s value: %s update: %s", $column->getName(), $column->getValue(), $column->getUpdated());
        }

    }
}