<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Traits;

use Illuminate\Http\JsonResponse;
use Jiannei\Response\Laravel\Support\Facades\Format;

trait JsonResponseTrait
{
    /**
     *  Respond with an accepted response and associate a location and/or content if provided.
     *
     * @param  array  $data
     */
    public function accepted($data = [], string $message = '', string $location = ''): JsonResponse
    {
        return tap($this->success($data, $message, 202), function ($response) use ($location) {
            if ($location) {
                $response->header('Location', $location);
            }
        });
    }

    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param  null  $data
     */
    public function created($data = [], string $message = '', string $location = ''): JsonResponse
    {
        return tap($this->success($data, $message, 201), function ($response) use ($location) {
            if ($location) {
                $response->header('Location', $location);
            }
        });
    }

    /**
     * Respond with a no content response.
     */
    public function noContent(string $message = ''): JsonResponse
    {
        return $this->success(message: $message, code: 204);
    }

    /**
     * Alias of success method, no need to specify data parameter.
     */
    public function ok(string $message = '', int|\BackedEnum $code = 200): JsonResponse
    {
        return $this->success(message: $message, code: $code);
    }

    /**
     * Alias of the successful method, no need to specify the message and data parameters.
     * You can use ResponseCodeEnum to localize the message.
     */
    public function localize(int|\BackedEnum $code = 200): JsonResponse
    {
        return $this->ok(code: $code);
    }

    /**
     * Return a 400 bad request error.
     */
    public function errorBadRequest(string $message = ''): JsonResponse
    {
        return $this->fail($message, 400);
    }

    /**
     * Return a 401 unauthorized error.
     */
    public function errorUnauthorized(string $message = ''): JsonResponse
    {
        return $this->fail($message, 401);
    }

    /**
     * Return a 403 forbidden error.
     */
    public function errorForbidden(string $message = ''): JsonResponse
    {
        return $this->fail($message, 403);
    }

    /**
     * Return a 404 not found error.
     */
    public function errorNotFound(string $message = ''): JsonResponse
    {
        return $this->fail($message, 404);
    }

    /**
     * Return a 405 method not allowed error.
     */
    public function errorMethodNotAllowed(string $message = ''): JsonResponse
    {
        return $this->fail($message, 405);
    }

    /**
     * Return an fail response.
     *
     * @param  null  $errors
     */
    public function fail(string $message = '', int|\BackedEnum $code = 500, $errors = null): JsonResponse
    {
        return Format::data(message: $message, code: $code, error: $errors)->response();
    }

    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @return JsonResponse
     */
    public function success($data = [], string $message = '', int|\BackedEnum $code = 200)
    {
        return Format::data(data: $data, message: $message, code: $code)->response();
    }
}
