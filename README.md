# ReactPHP Response Helper
This library provides beautiful syntax for [ReactPHP HTTP](https://reactphp.org/http)
component, this library provides syntax very similar to that of NodeJS.

## Installation
You will need [Composer](https://getcomposer.org) to install this library.

```bash
composer require ahmard/reactphp-response
```

## Usage

### Registering middleware
- Test\Counter

```php
namespace Test;

use Reaponse\Http\HandlerInterface;
use Reaponse\Http\ResponseInterface;

class CountHandler implements HandlerInterface
{
    protected static int $counts = 0;

    public function handle(ResponseInterface $response): void
    {
        self::$counts++;
        $response->write('Count: ' . self::$counts);
        $response->handler()->next();
    }
}
```

- Test\Server

```php
namespace Test;

use Reaponse\Http\HandlerInterface;
use Reaponse\Http\ResponseInterface;

class ServerHandler implements HandlerInterface
{
    public function handle(ResponseInterface $response): void
    {
        $response->html(', Time: ' . date('H:i:s'));
        $response->end('.');
    }
}
```

- server.php

```php
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
```

Start the server
```bash
php server.php
```


### Request object

```php
use Reaponse\Http\HandlerInterface;
use Reaponse\Http\Response;
use Reaponse\Http\ResponseInterface;


class TestHandler implements HandlerInterface
{
    protected static int $counts = 0;

    public function handle(ResponseInterface $response): void
    {
        //psr-7 compliant object
        $request = $response->request();
        
        $response->html("Method: {$request->getMethod()}<br/>");
        
        $response->end('Bye!');
    }
}
```

### Listens to response events

```php
use Reaponse\Http\HandlerInterface;
use Reaponse\Http\Response;
use Reaponse\Http\ResponseInterface;


class TestHandler implements HandlerInterface
{
    protected static int $counts = 0;

    public function handle(ResponseInterface $response): void
    {
        //listens to write event
        $response->on(Response::ON_WRITE, function (){
            echo "Writing...\n";
        });
        //Listens to headers event
        $response->on(Response::ON_HEADERS, function (){
            echo "Headers...\n";
        });
        //Listens to next handler event
        $response->on(Response::ON_NEXT_HANDLER, function (){
            echo "Next handler...\n";
        });
        //Listens to response sending event
        $response->on(Response::ON_BEFORE_SEND, function (){
            echo "Sending...\n";
        });
        
        $response->end('Hello World');
    }
}
```

### 

### [Example](example)


## Licence
**Reaponse** is **MIT** licenced.