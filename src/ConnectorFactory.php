<?php


namespace PhpOne\CanalPHP;


use PhpOne\CanalPHP\Connector\impl\SimpleConnector;

/**
 * Class ConnectorFactory
 * @package PhpOne\CanalPHP
 */
class ConnectorFactory
{

    /**
     * @param string $address
     * @param int $port
     * @param string $destination
     * @param string $username
     * @param String $password
     * @return SimpleConnector
     */
    public static function getSimpleConnector(string $address, int $port, string $destination, string $username = "", String $password = "")
    {
        $connector = new SimpleConnector($address, $port, $destination, $username, $password);
        return $connector;
    }

}