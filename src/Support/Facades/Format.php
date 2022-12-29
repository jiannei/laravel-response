<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static int statusCode(int $code)
 * @method static array data($data, $message, $code, $errors = null)
 * @method static array resourceCollection($resource, string $message = '', int $code = 200, array $headers = [], int $option = 0)
 * @method static array jsonResource($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
 * @method static array paginator($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
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
