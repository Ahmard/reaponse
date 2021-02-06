<?php


namespace Reaponse\Http;


use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Nette\Utils\Json;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Message\Response as ReactResponse;
use React\Promise\Deferred;
use Reaponse\ObjectStorage;
use RingCentral\Psr7\Stream;

class Response implements ResponseInterface
{
    /**
     * This event will be emitted when new data is written to the response stream
     */
    public const ON_WRITE = 'on.write';

    /**
     * This event will emitted when header is added to the response
     */
    public const ON_HEADERS = 'on.headers';

    /**
     * This event will be emitted before response is sent to the client
     */
    public const ON_BEFORE_SEND = 'on.before.send';

    /**
     * This event will be emitted when next handler is executed
     */
    public const ON_NEXT_HANDLER = 'on.next.middleware';


    protected const STREAM_FILE = 'php://temp';

    protected array $headers = [];

    protected array $status = [
        'code' => 200,
        'phrase' => 'OK'
    ];

    protected string $version = '1.1';

    protected array $values = [
        'headers' => [],
    ];

    protected StreamInterface $stream;
    protected Handler $handler;
    protected ServerRequestInterface $request;
    protected EventEmitterInterface $event;
    private Deferred $deferred;


    public function __construct(Deferred $deferred, ServerRequestInterface $request, array $handlers)
    {
        //Add event class to object storage
        ObjectStorage::set('reaponse.event', new EventEmitter());

        $this->deferred = $deferred;
        $this->handler = new Handler($handlers, $this);
        $this->request = $request;
    }

    public function handler(): Handler
    {
        return $this->handler;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function html(string $htmlCode): ResponseInterface
    {
        $this->header('Content-Type', 'text/html');
        return $this->write($htmlCode);
    }

    public function header($name, ?string $value = null): ResponseInterface
    {
        //Emit headers event
        ObjectStorage::get('reaponse.event')
            ->emit(self::ON_HEADERS, [$this->headers]);

        if (is_array($name)) {
            $this->headers = array_merge($this->headers, $name);
            return $this;
        }

        $this->headers[$name] = $value;
        return $this;
    }

    public function write($data): ResponseInterface
    {
        //emit write event
        ObjectStorage::get('reaponse.event')
            ->emit(self::ON_WRITE, [$data]);

        if (!is_scalar($data)) {
            return $this->json($data);
        }

        $this->getStream()->write($data);
        return $this;
    }

    public function json($arrayOrObject): ResponseInterface
    {
        $this->header('Content-Type', 'application/json');
        return $this->write(Json::encode($arrayOrObject));
    }

    protected function getStream(): StreamInterface
    {
        if (!isset($this->stream)) {
            $this->stream = new Stream(fopen(self::STREAM_FILE, 'w+'));
        }

        return $this->stream;
    }

    public function end($message = null): void
    {
        //emit before send event
        ObjectStorage::get('reaponse.event')
            ->emit(self::ON_BEFORE_SEND, [$message]);

        if (null !== $message) {
            if (!is_string($message)) {
                $message = Json::encode($message);
            }

            $this->getStream()->write($message);
        }

        $response = new ReactResponse(
            $this->status['code'],
            $this->headers,
            $this->stream,
            $this->version,
            $this->status['phrase']
        );

        $this->deferred->resolve($response);
    }

    public function status(int $code, ?string $phrase = null): ResponseInterface
    {
        $this->status = [
            'code' => $code,
            'phrase' => $phrase
        ];

        return $this;
    }

    public function version(string $version): ResponseInterface
    {
        $this->version = $version;
        return $this;
    }

    public function on(string $eventName, callable $listener): ResponseInterface
    {
        ObjectStorage::get('reaponse.event')
            ->on($eventName, $listener);
        return $this;
    }

    public function once(string $eventName, callable $listener): ResponseInterface
    {
        ObjectStorage::get('reaponse.event')
            ->once($eventName, $listener);
        return $this;
    }

    protected function createStream(string $body): StreamInterface
    {
        $stream = $this->getStream();
        $stream->write($body);
        $stream->rewind();
        return $stream;
    }
}