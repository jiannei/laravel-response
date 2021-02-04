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
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class Response
{
    /**
     *  Respond with an accepted response and associate a location and/or content if provided.
     *
     * @param  null  $data
     * @param  string  $message
     * @param  string  $location
     * @return JsonResponse|JsonResource
     */
    public function accepted($data = null, string $message = '', string $location = '')
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
    public function created($data = null, string $message = '', string $location = '')
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
        return $this->success(null, $message, 204);
    }

    /**
     * Return a 400 bad request error.
     *
     * @param  string|null  $message
     */
    public function errorBadRequest(?string $message = ''): void
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
     * @param  array|null  $errors
     * @param  array  $header
     * @param  int  $options
     *
     * @return JsonResponse
     * @throws HttpResponseException
     */
    public function fail(string $message = '', int $code = 500, $errors = null, array $header = [], int $options = 0)
    {
        $response = $this->response(
            $this->formatData(null, $message, $code, $errors),
            $code,
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
     * @param  JsonResource|array|null|mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     *
     * @return JsonResponse|JsonResource
     */
    public function success($data = null, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        if ($data instanceof ResourceCollection && ($data->resource instanceof AbstractPaginator)) {
            return $this->formatPaginatedResourceResponse(...func_get_args());
        }

        if ($data instanceof JsonResource) {
            return $this->formatResourceResponse(...func_get_args());
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
     *
     * @return JsonResponse
     */
    protected function formatArrayResponse(?array $data, string $message = '', $code = 200, array $headers = [], $option = 0): JsonResponse
    {
        return $this->response($this->formatData($data, $message, $code), $code, $headers, $option);
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

        return [
            'status' => $status,
            'code' => $originalCode,
            'message' => $message,
            'data' => $data ?: (object) $data,
            'error' =>  $errors ?: (object) [],
        ];
    }

    /**
     * Format paginated resource data.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     *
     * @return mixed
     */
    protected function formatPaginatedResourceResponse($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        $paginated = $resource->resource->toArray();
        $count = $paginated['total'] ?? null;
        $totalPages = $paginated['last_page'] ?? null;
        $previous = $paginated['prev_page_url'] ?? null;
        $next = $paginated['next_page_url'] ?? null;

        $paginationInformation = [
            'meta' => [
                'pagination' => [
                    'count' => $paginated['to'] ?? null,
                    'per_page' => $paginated['per_page'] ?? null,
                    'current_page' => $paginated['current_page'] ?? null,
                ],
            ],
        ];

        if ($count) {
            $paginationInformation['meta']['pagination']['total'] = $count;
        }

        if ($totalPages) {
            $paginationInformation['meta']['pagination']['total_pages'] = $totalPages;
        }

        if ($previous || $next) {
            $paginationInformation['meta']['pagination']['links'] = [
                'previous' => $previous,
                'next' => $next,
            ];
        }

        $paginatedResourceDataField = Config::get('response.format.paginated_resource.data_field', 'data');

        $data = array_merge_recursive([$paginatedResourceDataField => $this->parseDataFrom($resource)], $paginationInformation);

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
     *
     * @return mixed
     */
    protected function formatResourceResponse($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        return tap(
            $this->response($this->formatData($this->parseDataFrom($resource), $message, $code), $code, $headers, $option),
            function ($response) use ($resource) {
                $response->original = $resource->resource;

                $resource->withResponse(request(), $response);
            }
        );
    }

    /**
     * Parse data from JsonResource.
     *
     * @param  JsonResource  $data
     *
     * @return array
     */
    protected function parseDataFrom(JsonResource $data): array
    {
        return array_merge_recursive($data->resolve(request()), $data->with(request()), $data->additional);
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
    protected function response($data = [], $status = 200, array $headers = [], $options = 0): JsonResponse
    {
        return response()->json($data, $status, $headers, $options);
    }
}
