<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;
use Throwable;

class TestHandler extends BaseHandler
{
    use ExceptionTrait;

    public function publicPrepareJsonResponse(Request $request, Throwable $e)
    {
        return $this->prepareJsonResponse($request, $e);
    }

    public function publicInvalidJson(Request $request, ValidationException $exception)
    {
        return $this->invalidJson($request, $exception);
    }

    public function publicUnauthenticated(Request $request, AuthenticationException $exception)
    {
        return $this->unauthenticated($request, $exception);
    }
}
