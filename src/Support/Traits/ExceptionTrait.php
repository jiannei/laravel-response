<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Response;
use Throwable;

trait ExceptionTrait
{
    /**
     * Custom Normal Exception response.
     *
     * @param $request
     * @param  Throwable  $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        // 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
        // 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;
        $isHttpException = $this->isHttpException($e);

        return app(Response::class)->fail(
            $isHttpException ? $e->getMessage() : 'Server Error',
            $isHttpException ? $e->getStatusCode() : 500,
            $this->convertExceptionToArray($e),
            $isHttpException ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Custom Failed Validation Response for Lumen.
     *
     * @param  Request  $request
     * @param  array  $errors
     *
     * @return mixed
     * @throws HttpResponseException
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if (isset(static::$responseBuilder)) {
            return (static::$responseBuilder)($request, $errors);
        }

        $firstMessage = Arr::first($errors, null, '');

        return app(Response::class)->fail(
            is_array($firstMessage) ? Arr::first($firstMessage) : $firstMessage, Config::get('response.validation_error_code', 422),
            $errors
        );
    }

    /**
     * Custom Failed Validation Response for Laravel.
     *
     * @param  Request  $request
     * @param  ValidationException  $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return app(Response::class)->fail(
            $exception->validator->errors()->first(),
            Config::get('response.validation_error_code', $exception->status),
            $exception->errors()
        );
    }

    /**
     * Custom Failed Authentication Response for Laravel.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return \Illuminate\Http\RedirectResponse | JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? app(Response::class)->errorUnauthorized($exception->getMessage())
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
