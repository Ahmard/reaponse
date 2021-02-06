<?php


namespace Reaponse;


/**
 * Class ObjectStorage
 * @package Reaponse\Http
 * @internal For internal use only
 */
class ObjectStorage
{
    protected static array $data;


    public static function set(string $key, object $emitter): void
    {
        self::$data[$key] = $emitter;
    }

    public static function get(string $key): object
    {
        return self::$data[$key];
    }
}