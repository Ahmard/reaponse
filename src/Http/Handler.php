<?php


namespace Reaponse\Http;


use Reaponse\ObjectStorage;
use SplQueue;

/**
 * Class Handler
 * @package Reaponse\Http
 * @internal For internal use only
 */
class Handler
{
    /**
     * @var SplQueue<HandlerInterface>
     */
    protected SplQueue $handlers;

    protected ResponseInterface $response;


    public function __construct(array $handlers, ResponseInterface $response)
    {
        $this->response = $response;
        $this->handlers = new SplQueue();
        foreach ($handlers as $handler) {
            $this->handlers->push($handler);
        }

        $this->handlers->rewind();
    }

    public function next(): void
    {
        //Emit next middleware event
        ObjectStorage::get('reaponse.event')
            ->emit(Response::ON_NEXT_HANDLER, []);

        $this->handlers->next();
        $this->handlers->current()->handle($this->response);
    }

    /**
     * @return  SplQueue<HandlerInterface>
     */
    public function getQueue(): SplQueue
    {
        return $this->handlers;
    }
}