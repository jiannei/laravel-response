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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Jiannei\Response\Laravel\Support\Facades\Format;

trait JsonResponseTrait
{
    /**
     *  Respond with an accepted response and associate a location and/or content if provided.
     *
     * @param  array  $data
     * @param  string  $message
     * @param  string  $location
     * @return JsonResponse
     */
    public function accepted($data = [], string $message = '', string $location = ''): JsonResponse
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
     * @param  null  $data
     * @param  string  $message
     * @param  string  $location
     * @return JsonResponse
     */
    public function created($data = [], string $message = '', string $location = ''): JsonResponse
    {
        $response = $this->success($data, $message, 201);
        if ($location) {
            $response->header('Location', $location);
        }

        return $response;
    }

    /**
     * Respond with a no content response.
     *
     * @param  string  $message
     * @return JsonResponse
     */
    public function noContent(string $message = ''): JsonResponse
    {
        return $this->success([], $message, 204);
    }

    /**
     * Alias of success method, no need to specify data parameter.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse
     */
    public function ok(string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        return $this->success([], $message, $code, $headers, $option);
    }

    /**
     * Alias of the successful method, no need to specify the message and data parameters.
     * You can use ResponseCodeEnum to localize the message.
     *
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse
     */
    public function localize(int $code = 200, array $headers = [], int $option = 0)
    {
        return $this->ok('', $code, $headers, $option);
    }

    /**
     * Return a 400 bad request error.
     *
     * @param  string|null  $message
     */
    public function errorBadRequest(string $message = ''): void
    {
        $this->fail($message, 400);
    }

    /**
     * Return a 401 unauthorized error.
     *
     * @param  string  $message
     */
    public function errorUnauthorized(string $message = ''): void
    {
        $this->fail($message, 401);
    }

    /**
     * Return a 403 forbidden error.
     *
     * @param  string  $message
     */
    public function errorForbidden(string $message = ''): void
    {
        $this->fail($message, 403);
    }

    /**
     * Return a 404 not found error.
     *
     * @param  string  $message
     */
    public function errorNotFound(string $message = ''): void
    {
        $this->fail($message, 404);
    }

    /**
     * Return a 405 method not allowed error.
     *
     * @param  string  $message
     */
    public function errorMethodNotAllowed(string $message = ''): void
    {
        $this->fail($message, 405);
    }

    /**
     * Return a 500 internal server error.
     *
     * @param  string  $message
     */
    public function errorInternal(string $message = ''): void
    {
        $this->fail($message);
    }

    /**
     * Return an fail response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  null  $errors
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse
     *
     */
    public function fail(string $message = '', int $code = 500, $errors = null, array $headers = [], int $option = 0): JsonResponse
    {
        $response = Format::response(null, $message, $code, $errors, $headers, $option, 'fail');

        if (is_null($errors)) {
            $response->throwResponse();
        }

        return $response;
    }

    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse
     */
    public function success($data = [], string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        return Format::response(...func_get_args());
    }
}
