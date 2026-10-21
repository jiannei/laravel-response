<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static JsonResponse accepted(array<string, mixed> $data = [], string $message = '', string $location = '')
 * @method static JsonResponse created(array<string, mixed> $data = [], string $message = '', string $location = '')
 * @method static JsonResponse noContent(string $message = '')
 * @method static JsonResponse localize(int|\BackedEnum $code = 200)
 * @method static JsonResponse ok(string $message = '', int|\BackedEnum $code = 200)
 * @method static JsonResponse success(mixed $data = [], string $message = '', int|\BackedEnum $code = 200)
 * @method static JsonResponse errorBadRequest(string $message = '')
 * @method static JsonResponse errorUnauthorized(string $message = '')
 * @method static JsonResponse errorForbidden(string $message = '')
 * @method static JsonResponse errorNotFound(string $message = '')
 * @method static JsonResponse errorMethodNotAllowed(string $message = '')
 * @method static JsonResponse errorInternal(string $message = '')
 * @method static JsonResponse fail(string $message = '', int|\BackedEnum $code = 500, array<string, mixed>|object|null $errors = null)
 *
 * @see \Jiannei\Response\Laravel\Response
 */
class Response extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return \Jiannei\Response\Laravel\Response::class;
    }
}
