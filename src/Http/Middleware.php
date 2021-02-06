<?php


namespace Reaponse\Http;


use Nette\Utils\JsonException;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Middleware
{
    protected array $handlers;


    /**
     * Middleware constructor.
     * @param HandlerInterface ...$handler
     */
    public function __construct(...$handler)
    {
        $this->handlers = $handler;
    }

    /**
     * @param ServerRequestInterface $request
     * @return PromiseInterface
     * @throws JsonException
     */
    public function __invoke(ServerRequestInterface $request): PromiseInterface
    {
        $deferred = new Deferred();
        $promise = $deferred->promise();
        $response = new Response($deferred, $request, $this->handlers);

        $this->handlers[0]->handle($response);
        return $promise;
    }
}