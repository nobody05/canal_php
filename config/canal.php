<?php

return [
    "server" => [
        "address" => '127.0.0.1',
        "port" => 11111,
        "destination" => "example",
        "username" => "",
        "password" => "",
        "filter" => "test.user",// 订阅规则 db.table
        "clientId" => 1001,// 默认值
        // 参见  https://wiki.swoole.com/#/runtime
        "openCoroutine" => true, //是否开启协程
        "coHookFlags" => "SWOOLE_HOOK_ALL", // 协程hook
    ],
    "batchSize" => 100,//每次拉取的数量值
    "maxWhileCount" => 100,
    "openMessagePrint" => true,
    "messageCallback" => [\PhpOne\CanalPHP\Test\MessageHandle::class, "handle"],
    "client" => [
        'open_length_check' => 1, // 是否开启包长度检测
        "package_length_offset" => 0, // 从哪个字节开始算包长，如果总长度包含包头，就从0开始
        "package_body_offset" => 4, // 哪个字节的值代表包体长度
        'package_length_type' => 'N' // 解包方式  N 代表32位  参考pack unpack函数
    ]
];