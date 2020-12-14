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

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Jiannei\Response\Laravel\Response;
use Throwable;

trait ExceptionTrait
{
    /**
     * Custom Normal Exception response.
     *
     * @param $request
     * @param  Throwable  $e
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        // 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
        // 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;
        return app(Response::class)->fail(
            '',
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            config('app.debug', false) ? $this->convertExceptionToArray($e) : [],
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Custom Failed Validation Response.
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

        return app(Response::class)->fail('', Config::get('response.validation_error_code', 422), $errors);
    }
}
