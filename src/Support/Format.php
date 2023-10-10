<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Macroable;

class Format implements \Jiannei\Response\Laravel\Contracts\Format
{
    use Macroable;

    /**
     * Return a new JSON response from the application.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    public function response($data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return new JsonResponse($data, $this->formatStatusCode($status), $headers, $options);
    }

    /**
     * Format return data structure.
     *
     * @param  array|null  $data
     * @param  string|null  $message
     * @param  int  $code
     * @param  null  $errors
     * @return array
     */
    public function data(?array $data, ?string $message, int $code, $errors = null): array
    {
        return $this->formatDataFields([
            'status' => $this->formatStatus($code),
            'code' => $code,
            'message' => $this->formatMessage($code, $message),
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
        ]);
    }

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator|AbstractCursorPaginator  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return array
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array
    {
        $paginated = $resource->toArray();

        $paginationInformation = $this->formatPaginatedData($paginated);

        $data = array_merge_recursive(['data' => $paginated['data']], $paginationInformation);

        return $this->data($data, $message, $code);
    }

    /**
     * Format collection resource data.
     *
     * @param  ResourceCollection  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return array
     */
    public function resourceCollection(ResourceCollection $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array
    {
        $data = array_merge_recursive(['data' => $resource->resolve(request())], $resource->with(request()), $resource->additional);
        if ($resource->resource instanceof AbstractPaginator || $resource->resource instanceof AbstractCursorPaginator) {
            $paginated = $resource->resource->toArray();
            $paginationInformation = $this->formatPaginatedData($paginated);

            $data = array_merge_recursive($data, $paginationInformation);
        }

        return $this->data($data, $message, $code);
    }

    /**
     * Format JsonResource Data.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return array
     */
    public function jsonResource(JsonResource $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array
    {
        $resourceData = array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);

        return $this->data($resourceData, $message, $code);
    }

    /**
     * Format return message.
     *
     * @param  int  $code
     * @param  string|null  $message
     * @return string
     */
    protected function formatMessage(int $code, ?string $message): ?string
    {
        if (! $message && class_exists($enumClass = Config::get('response.enum'))) {
            $message = $enumClass::fromValue($code)->description;
        }

        return $message;
    }

    /**
     * Format http status description.
     *
     * @param  int  $code
     * @return string
     */
    protected function formatStatus(int $code): string
    {
        $statusCode = $this->formatStatusCode($code);
        if ($statusCode >= 400 && $statusCode <= 499) {// client error
            $status = 'error';
        } elseif ($statusCode >= 500 && $statusCode <= 599) {// service error
            $status = 'fail';
        } else {
            $status = 'success';
        }

        return $status;
    }

    /**
     * Http status code.
     *
     * @param  $code
     * @return int
     */
    protected function formatStatusCode($code): int
    {
        return (int) substr($code, 0, 3);
    }

    /**
     * Format paginated data.
     *
     * @param  array  $paginated
     * @return array
     */
    protected function formatPaginatedData(array $paginated): array
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
                    'cursor' => [
                        'previous' => $paginated['prev_cursor'] ?? '',
                        'next' => $paginated['next_cursor'] ?? '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Format response data fields.
     *
     * @param  array  $data
     * @return array
     */
    protected function formatDataFields(array $data): array
    {
        $formatConfig = \config('response.format.config', []);

        foreach ($formatConfig as $key => $config) {
            if (! Arr::has($data, $key)) {
                continue;
            }

            $show = $config['show'] ?? true;
            $alias = $config['alias'] ?? '';

            if ($alias && $alias !== $key) {
                Arr::set($data, $alias, Arr::get($data, $key));
                $data = Arr::except($data, $key);
                $key = $alias;
            }

            if (! $show) {
                $data = Arr::except($data, $key);
            }
        }

        return $data;
    }
}
