<?php


namespace Reaponse\Http;


use InvalidArgumentException;
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
        if (empty($handler)) {
            throw new InvalidArgumentException('Server handlers must be provided.');
        }

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

        $handler = $this->handlers[0];

        if (!$handler instanceof HandlerInterface) {
            $handlerStr = get_class($handler);
            throw new InvalidArgumentException("Handler {$handlerStr} must implement Reaponse\Http\HandlerInterface");
        }

        $handler->handle($response);
        return $promise;
    }
}