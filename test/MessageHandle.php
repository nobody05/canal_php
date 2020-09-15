<?php

namespace PhpOne\CanalPHP\Test;

class MessageHandle
{

    public function handle($messages)
    {
        echo "receive". count($messages). PHP_EOL;
    }
}