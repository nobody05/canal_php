<?php


namespace PhpOne\CanalPHP\Connector;

/**
 * Interface Connector
 * @package PhpOne\CanalPHP\Connector
 */
interface Connector
{
    public function connect();
    public function disconnect();
    public function subscribe(string $filter);
    public function checkValid();
    public function unsubscribe();
    public function get();
    public function getWithoutAck(int $batchSize);
    public function ack($batchId);
    public function rollback();

}