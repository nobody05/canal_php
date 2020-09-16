<?php

namespace PhpOne\CanalPHP\Test;

class MessageHandle
{

    public static function handle($messages = [])
    {
        foreach ($messages as $message) {
            echo "tableName: ". $message['tableName']. "  event:  ". $message['eventTypeName'];
        }
    }
}