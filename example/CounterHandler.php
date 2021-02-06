<?php


namespace Test;


use Reaponse\Http\HandlerInterface;
use Reaponse\Http\Response;
use Reaponse\Http\ResponseInterface;

class CounterHandler implements HandlerInterface
{
    protected static int $counts = 0;

    public function handle(ResponseInterface $response): void
    {
        self::$counts++;
        $response->write('Count: ' . self::$counts);

        $response->on(Response::ON_WRITE, function (){
            echo "Writing...";
        });

        $response->on(Response::ON_HEADERS, function (){
            echo "Headers...";
        });

        $response->on(Response::ON_NEXT_HANDLER, function (){
            echo "Next...";
        });

        $response->on(Response::ON_BEFORE_SEND, function (){
            echo "Sending...";
        });

        $response->handler()->next();
    }
}