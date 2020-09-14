<?php

require __DIR__. "/../vendor/autoload.php";

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect("127.0.0.1", '11111', 10)) {

    throw new Exception("conn err". $client->errCode);
}

$client->set([
    "package_length_offset" => 1,
    "package_body_offset" => 0,
    'open_length_check' => true,
    'package_length_type' => 'N'
]);
//while (true) {

// 握手

    $len = $client->recv(4);
    $bodyL = unpack("N", $len)[1];

    echo $bodyL. PHP_EOL;


    $body = $client->recv($bodyL,true);

    echo $client->errCode. PHP_EOL;



//    var_dump($body);

//    $body = unpack("N", $body);
//    $message = new \Com\Alibaba\Otter\Canal\Protocol\Messages();

    $package = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
    $package->mergeFromString($body);

    var_dump($package->getType());
    var_dump($package->getVersion());

//    $hands = new \Com\Alibaba\Otter\Canal\Protocol\Handshake();
//    $hands->mergeFromString($package->getBody());
//
//    var_dump($hands->getSeeds());
//    var_dump($hands->getCommunicationEncoding());



//    $client->close();
//    return;

    // 认证

$package1 = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
$package1->setType(2);

$clientAuth = new \Com\Alibaba\Otter\Canal\Protocol\ClientAuth();
$clientAuth->setClientId(1001);
$clientAuth->setDestination("example");

$package1->setBody($clientAuth->serializeToString());

$sD = $package1->serializeToString();
$client->send(pack("N", strlen($sD)));
$client->send($sD);

echo "send auth". PHP_EOL;

// 接收认证结果
$len = $client->recv(4);
$bodyL = unpack("N", $len)[1];
$body = $client->recv($bodyL, true);

echo "get auth receive". PHP_EOL;
var_dump($body);
//    $message = new \Com\Alibaba\Otter\Canal\Protocol\Messages();

$pack2 = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
$pack2->mergeFromString($body);

var_dump($pack2->getVersion());
var_dump($pack2->getType());

$ack = new \Com\Alibaba\Otter\Canal\Protocol\Ack();
$ack->mergeFromString($pack2->getBody());

var_dump($ack->getErrorMessage());
var_dump($ack->getErrorCode());


echo "after ack". PHP_EOL;

// 订阅
    $pack3 = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
    $pack3->setType(4);
    $subs = new \Com\Alibaba\Otter\Canal\Protocol\Sub();
    $subs->setDestination("example");
    $subs->setClientId(1001);
    $subs->setFilter("test.student");
    $pack3->setBody($subs->serializeToString());

    $sD = $pack3->serializeToString();
    $client->send(pack("N", strlen($sD)));
    $client->send($sD);

echo "after send sub". PHP_EOL;

//  接收订阅结果
$len = $client->recv(4);
$bodyL = unpack("N", $len)[1];
$body = $client->recv($bodyL, true);
//    $message = new \Com\Alibaba\Otter\Canal\Protocol\Messages();

echo "receive sub result". PHP_EOL;
var_dump($body);


$pack = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
$pack->mergeFromString($body);

$ack = new \Com\Alibaba\Otter\Canal\Protocol\Ack();
$ack->mergeFromString($pack->getBody());
var_dump($ack->getErrorMessage());
var_dump($ack->getErrorCode());


// rollback

$pack = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
$pack->setType(12);
$cRollback = new \Com\Alibaba\Otter\Canal\Protocol\ClientRollback();
$cRollback->setClientId(1001);
$cRollback->setDestination("example");
$cRollback->setBatchId(10);
$pack->setBody($cRollback->serializeToString());
$sD = $pack->serializeToString();
$client->send(pack("N", strlen($sD)));
$client->send($sD);


echo "rollback send after". PHP_EOL;

// 接收rollback




// get
$count = 0;
$totalCount = 50;

$result = 0;
while ($count <= 50) {
    echo "while". PHP_EOL;
// get
    $pack = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
    $pack->setType(6);
    $get = new \Com\Alibaba\Otter\Canal\Protocol\Get();
    $get->setDestination("example");
    $get->setClientId(1001);
    $get->setAutoAck(true);
    $get->setFetchSize(10);
    $get->setTimeout(50);

    $pack->setBody($get->serializeToString());
    $sD = $pack->serializeToString();
    $client->send(pack("N", strlen($sD)));
    $client->send($sD);

    echo "sendAfter get". PHP_EOL;

    $len = $client->recv(4);
    $bodyL = unpack("N", $len)[1];
    $body = $client->recv($bodyL);


    $pack = new \Com\Alibaba\Otter\Canal\Protocol\Packet();
    $pack->mergeFromString($body);

    var_dump($pack->getType());

    $list = [];
//    $OMessagee = new \Google\Protobuf\Internal\Message();
    switch ($pack->getType()) {
        case 7:
            echo "has msg". PHP_EOL;
            $messages = new \Com\Alibaba\Otter\Canal\Protocol\Messages();
            $messages->mergeFromString($pack->getBody());

            print_r($messages->getBatchId());

            if ($messages->getBatchId()<=0) break;
//            $message = new \Google\Protobuf\Internal\Message($messages->getBatchId());
            $mI = $messages->getMessages()->getIterator();


            foreach ($mI as $k=>$m) {
                $entry = new \Com\Alibaba\Otter\Canal\Protocol\Entry();
                $entry->mergeFromString($m);


                $rowChange = new \Com\Alibaba\Otter\Canal\Protocol\RowChange();
                $rowChange->mergeFromString($entry->getStoreValue());
                $eventType = $rowChange->getEventType();
                $header =  $entry->getHeader();

                print_r($header->getTableName());
                print_r($header->getEventType());
                echo $rowChange->getSql(). PHP_EOL;
//                var_dump($entry->);

                $list[] = $entry;
            }

//            print_r($list);

            break;
        case 3:
            $ack = new \Com\Alibaba\Otter\Canal\Protocol\Ack();
            print_r($ack->getErrorCode());
            break;

    }

    sleep(1);

    echo "wait". PHP_EOL;

    $count++;
}


//$get = new \Com\Alibaba\Otter\Canal\Protocol\Get();
//$get->setDestination("example");
//$get->setClientId(1001);




    $client->close();

//}