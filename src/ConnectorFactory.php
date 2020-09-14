<?php


namespace PhpOne\CanalPHP;


use PhpOne\CanalPHP\Connector\impl\SimpleConnector;


class ConnectorFactory
{

    public static function getSimpleConnector(string $address, string $port, string $destination, string $username = "", String $password = "")
    {
        $connector = new SimpleConnector($address, $port, $destination, $username, $password);
        return $connector;
    }

}