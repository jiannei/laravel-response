<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel;

use Illuminate\Http\JsonResponse;
use Jiannei\Response\Laravel\Support\Format;

class Response
{
    public function __construct(protected Format $format) {}

    /**
     * Respond with an accepted response and associate a location if provided.
     *
     * @param  array<string, mixed>  $data
     */
    public function accepted(array $data = [], string $message = '', string $location = ''): JsonResponse
    {
        $response = $this->success($data, $message, 202);

        if ($location) {
            $response->header('Location', $location);
        }

        return $response;
    }

    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param  array<string, mixed>  $data
     */
    public function created(array $data = [], string $message = '', string $location = ''): JsonResponse
    {
        $response = $this->success($data, $message, 201);

        if ($location) {
            $response->header('Location', $location);
        }

        return $response;
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
     * Return a 500 internal server error.
     */
    public function errorInternal(string $message = ''): JsonResponse
    {
        return $this->fail($message, 500);
    }

    /**
     * Return a failed response.
     *
     * @param  array<string, mixed>|object|null  $errors
     */
    public function fail(string $message = '', int|\BackedEnum $code = 500, array|object|null $errors = null): JsonResponse
    {
        return $this->format->make(message: $message, code: $code, error: $errors);
    }

    /**
     * Return a successful response.
     */
    public function success(mixed $data = [], string $message = '', int|\BackedEnum $code = 200): JsonResponse
    {
        return $this->format->make(data: $data, message: $message, code: $code);
    }
}
