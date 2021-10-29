<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\Resource as JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class Response
{
    /**
     *  Respond with an accepted response and associate a location and/or content if provided.
     *
     * @param  array  $data
     * @param  string  $message
     * @param  string  $location
     * @return JsonResponse|JsonResource
     */
    public function accepted($data = [], string $message = '', string $location = '')
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
     * @return JsonResponse|JsonResource
     */
    public function created($data = [], string $message = '', string $location = '')
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
     * @return JsonResponse|JsonResource
     */
    public function noContent(string $message = '')
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
     * @return JsonResponse|JsonResource
     */
    public function ok(string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        return $this->success([], $message, $code, $headers, $option);
    }

    /**
     * Return a 400 bad request error.
     *
     * @param  string|null  $message
     */
    public function errorBadRequest(string $message = '')
    {
        $this->fail($message, 400);
    }

    /**
     * Return a 401 unauthorized error.
     *
     * @param  string  $message
     */
    public function errorUnauthorized(string $message = '')
    {
        $this->fail($message, 401);
    }

    /**
     * Return a 403 forbidden error.
     *
     * @param  string  $message
     */
    public function errorForbidden(string $message = '')
    {
        $this->fail($message, 403);
    }

    /**
     * Return a 404 not found error.
     *
     * @param  string  $message
     */
    public function errorNotFound(string $message = '')
    {
        $this->fail($message, 404);
    }

    /**
     * Return a 405 method not allowed error.
     *
     * @param  string  $message
     */
    public function errorMethodNotAllowed(string $message = '')
    {
        $this->fail($message, 405);
    }

    /**
     * Return a 500 internal server error.
     *
     * @param  string  $message
     */
    public function errorInternal(string $message = '')
    {
        $this->fail($message);
    }

    /**
     * Return an fail response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|null  $errors
     * @param  array  $header
     * @param  int  $options
     * @return JsonResponse
     *
     * @throws HttpResponseException
     */
    public function fail(string $message = '', int $code = 500, $errors = null, array $header = [], int $options = 0)
    {
        $response = $this->response(
            $this->formatData(null, $message, $code, $errors),
            Config::get('response.error_code') ?: $code,
            $header,
            $options
        );

        if (is_null($errors)) {
            $response->throwResponse();
        }

        return $response;
    }

    /**
     * Return an success response.
     *
     * @param  JsonResource|array|mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse|JsonResource
     */
    public function success($data = [], string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        if ($data instanceof ResourceCollection) {
            return $this->formatResourceCollectionResponse(...func_get_args());
        }

        if ($data instanceof JsonResource) {
            return $this->formatResourceResponse(...func_get_args());
        }

        if ($data instanceof AbstractPaginator) {
            return $this->formatPaginatedResponse(...func_get_args());
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return $this->formatArrayResponse(Arr::wrap($data), $message, $code, $headers, $option);
    }

    /**
     * Format normal array data.
     *
     * @param  array|null  $data
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return JsonResponse
     */
    protected function formatArrayResponse(array $data, string $message = '', int $code = 200, array $headers = [], int $option = 0): JsonResponse
    {
        return $this->response($this->formatData($data, $message, $code), $code, $headers, $option);
    }

    /**
     * Format response data fields.
     *
     * @param  array  $responseData
     * @param  array  $dataFieldsConfig
     * @return array
     */
    protected function formatDataFields(array $responseData, array $dataFieldsConfig = []): array
    {
        if (empty($dataFieldsConfig)) {
            return $responseData;
        }

        foreach ($responseData as $field => $value) {
            $fieldConfig = Arr::get($dataFieldsConfig, $field);
            if (is_null($fieldConfig)) {
                continue;
            }

            if ($value && is_array($value) && in_array($field, ['data', 'meta', 'pagination', 'links'])) {
                $value = $this->formatDataFields($value, Arr::get($dataFieldsConfig, "{$field}.fields", []));
            }

            $alias = $fieldConfig['alias'] ?? $field;
            $show = $fieldConfig['show'] ?? true;
            unset($responseData[$field]);

            if ($show) {
                $responseData[$alias] = $value;
            }
        }

        return $responseData;
    }

    /**
     * Format return data structure.
     *
     * @param  JsonResource|array|null  $data
     * @param $message
     * @param $code
     * @param  null  $errors
     * @return array
     */
    protected function formatData($data, $message, &$code, $errors = null): array
    {
        $originalCode = $code;
        $code = (int) substr($code, 0, 3); // notice
        if ($code >= 400 && $code <= 499) {// client error
            $status = 'error';
        } elseif ($code >= 500 && $code <= 599) {// service error
            $status = 'fail';
        } else {
            $status = 'success';
        }

        if (! $message && class_exists($enumClass = Config::get('response.enum'))) {
            $message = $enumClass::fromValue($originalCode)->description;
        }

        return $this->formatDataFields([
            'status' => $status,
            'code' => $originalCode,
            'message' => $message,
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
        ], Config::get('response.format.fields', []));
    }

    /**
     * Format paginated response.
     *
     * @param  AbstractPaginator  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return mixed
     */
    protected function formatPaginatedResponse($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        $paginated = $resource->toArray();

        $paginationInformation = $this->formatPaginatedData($paginated);

        $data = array_merge_recursive(['data' => $paginated['data']], $paginationInformation);

        return $this->response($this->formatData($data, $message, $code), $code, $headers, $option);
    }

    /**
     * Format paginated data.
     *
     * @param  array  $paginated
     * @return array
     */
    protected function formatPaginatedData(array $paginated)
    {
        return [
            'meta' => [
                'pagination' => [
                    'total' => $paginated['total'] ?? 0,
                    'count' => $paginated['to'] ?? 0,
                    'per_page' => $paginated['per_page'] ?? 0,
                    'current_page' => $paginated['current_page'] ?? 0,
                    'total_pages' => $paginated['last_page'] ?? 0,
                    'links' => [
                        'previous' => $paginated['prev_page_url'] ?? '',
                        'next' => $paginated['next_page_url'] ?? '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Format collection resource response.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return mixed
     */
    protected function formatResourceCollectionResponse($resource, string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        if ($resource->resource instanceof AbstractPaginator) {
            $paginated = $resource->resource->toArray();
            $paginationInformation = $this->formatPaginatedData($paginated);

            $data = array_merge_recursive(['data' => $resource->resolve(request())], $resource->with(request()), $resource->additional, $paginationInformation);
        }else{
            $data = array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);
        }

        return tap(
            $this->response($this->formatData($data, $message, $code), $code, $headers, $option),
            function ($response) use ($resource) {
                $response->original = $resource->resource->map(
                    function ($item) {
                        return is_array($item) ? Arr::get($item, 'resource') : $item->resource;
                    }
                );

                $resource->withResponse(request(), $response);
            }
        );
    }

    /**
     * Format JsonResource Data.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return mixed
     */
    protected function formatResourceResponse($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        $resourceData = array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);

        return tap(
            $this->response($this->formatData($resourceData, $message, $code), $code, $headers, $option),
            function ($response) use ($resource) {
                $response->original = $resource->resource;

                $resource->withResponse(request(), $response);
            }
        );
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    protected function response($data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $options);
    }
}
