<?php

use React\EventLoop\Factory;
use React\Socket\Server;
use Reaponse\Http\Middleware;

require 'vendor/autoload.php';

$loop = Factory::create();
$uri = '0.0.0.0:9200';

$myServer = new \Test\ServerHandler();
$myCounter = new \Test\CounterHandler();

$httpServer = new \React\Http\Server($loop, new Middleware($myCounter, $myServer));
$socketServer = new Server($uri, $loop);

$httpServer->listen($socketServer);
$httpServer->on('error', function (Throwable $throwable){
    echo $throwable;
});

echo "Server started at http://{$uri}\n";
$loop->run();