<?php


namespace Reaponse\Http;


use Nette\Utils\JsonException;

interface HandlerInterface
{
    /**
     * @param ResponseInterface $response
     * @throws JsonException
     */
    public function handle(ResponseInterface $response): void;
}