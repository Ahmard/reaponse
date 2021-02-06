<?php

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