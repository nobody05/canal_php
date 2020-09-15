<?php

require __DIR__ . '/../../vendor/autoload.php';

!defined("BASE_DIR") && define("BASE_DIR", dirname(__DIR__, 2));
!defined("CANAL_CONFIG_DIR") && define("CANAL_CONFIG_DIR", BASE_DIR.DIRECTORY_SEPARATOR.'config');

(new \PhpOne\CanalPHP\CanalClient())->start();