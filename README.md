# canal_php
canal php client


## Install
```shell
composer require phpone/canal_php
```

## 使用
- config/canal.php 文件复制到项目目录，并修改自己的配置
- test/bin/start.php 文件复制到项目目录

## 启动
```shell
php bin/start.php 
```
请使用supervisor配置脚本，防止异常退出

## 配置说明
```php
return [
    // server相关配置
    "server" => [
        // server地址
        "address" => '127.0.0.1',
        // 端口号
        "port" => 11111,
        // server配置destination
        "destination" => "example",
        "username" => "",
        "password" => "",
        // 订阅规则 db.table
        "filter" => "test.user",
        // clientid 默认值
        "clientId" => 1001,
    ],
    //每次拉取的数量值
    "batchSize" => 100,
    // 空循环次数
    "maxWhileCount" => 100,
    // 是否打印message 开启后将输出到console
    "openMessagePrint" => true,
    // 业务处理逻辑写到这里
    "messageCallback" => [\PhpOne\CanalPHP\Test\MessageHandle::class, "handle"],
    // 处理tcp粘包 参照swoole配置  https://wiki.swoole.com/#/learn?id=tcp%e7%b2%98%e5%8c%85%e9%97%ae%e9%a2%98
    "client" => [
        'open_length_check' => 1, // 是否开启包长度检测
        "package_length_offset" => 0, // 从哪个字节开始算包长，如果总长度包含包头，就从0开始
        "package_body_offset" => 4, // 哪个字节的值代表包体长度
        'package_length_type' => 'N' // 解包方式  N 代表32位  参考pack unpack函数
    ]
];

```
## 处理逻辑
主要处理流程根据java版的canal_client参考来完成，其实就是c/s完成tcp数据传输的过程，我们需要了解protocolBuf的基本使用，
重点是canalServer中netty对于tcp的包处理，固定包头+包体，以便于我们client接收到之后进行数据处理。

client->protocol->tcp->server->tcp->protocol->client




