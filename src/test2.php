<?php

require __DIR__ . "/../vendor/autoload.php";

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);

// 处理tcp粘包
$client->set([
    'open_length_check' => 1, // 是否开启包长度检测
    "package_length_offset" => 0, // 从哪个字节开始算包长，如果总长度包含包头，就从0开始
    "package_body_offset" => 4, // 哪个字节的值代表包体长度
    'package_length_type' => 'N' // 解包方式  N 代表32位  参考pack unpack函数
]);


if (!$client->connect("127.0.0.1", '11111', 10)) {

    throw new Exception("conn err" . $client->errCode);
}

echo "receive.....". PHP_EOL;


$body = $client->recv();

var_dump($body);


$header = unpack("N", $body);
$content = substr($body, 4, reset($header));

var_dump($content);






$client->close();