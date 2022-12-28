<?php

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static int statusCode(int $code)
 * @method static array data($data, $message, $code, $errors = null)
 * @method static mixed resourceCollection($resource, string $message = '', int $code = 200, array $headers = [], int $option = 0)
 * @method static mixed jsonResource($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
 * @method static mixed paginator($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
 *
 * @see \Jiannei\Response\Laravel\Support\Format
 */
class Format extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return \Jiannei\Response\Laravel\Support\Format::class;
    }
}