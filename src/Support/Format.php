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

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Traits\Macroable;

class Format
{
    use Macroable;

    /**
     * Return a new JSON response from the application.
     *
     * @param  mixed|null  $data
     * @param  string  $message
     * @param  int|\BackedEnum  $code
     * @param  null  $errors
     * @param  array  $headers
     * @param  int  $option
     * @param  string  $from
     * @return JsonResponse
     */
    public function response(
        mixed $data = null,
        string $message = '',
        int|\BackedEnum $code = 200,
        $errors = null,
        array $headers = [],
        int $option = 0,
        string $from = 'success'
    ): JsonResponse {
        return new JsonResponse(
            $this->data($data, $message, $code, $errors),
            $this->formatStatusCode($code, $from),
            $headers,
            $option
        );
    }

    /**
     * Format return data structure.
     *
     * @param  JsonResource|array|mixed  $data
     * @param  string|null  $message
     * @param  int|\BackedEnum  $code
     * @param  null  $errors
     * @return array
     */
    public function data($data, ?string $message, int|\BackedEnum $code, $errors = null): array
    {
        $data = match (true) {
            $data instanceof ResourceCollection => $this->resourceCollection($data),
            $data instanceof JsonResource => $this->jsonResource($data),
            $data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator => $this->paginator($data),
            $data instanceof Arrayable || (is_object($data) && method_exists($data, 'toArray')) => $data->toArray(),
            default => Arr::wrap($data)
        };

        return $this->formatDataFields([
            'status' => $this->formatStatus($code),
            'code' => $this->formatBusinessCode($code),
            'message' => $this->formatMessage($code, $message),
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
        ]);
    }

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator|AbstractCursorPaginator  $resource
     * @return array
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator $resource): array
    {
        return [
            'data' => $resource->toArray()['data'],
            'meta' => $this->formatMeta($resource),
        ];
    }

    /**
     * Format collection resource data.
     *
     * @param  ResourceCollection  $collection
     * @return array
     */
    public function resourceCollection(ResourceCollection $collection): array
    {
        return array_filter([
            'data' => $collection->resolve(),
            'meta' => $this->formatMeta($collection->resource),
        ]);
    }

    /**
     * Format JsonResource Data.
     *
     * @param  JsonResource  $resource
     * @return array
     */
    public function jsonResource(JsonResource $resource): array
    {
        return value($this->formatJsonResource(), $resource);
    }

    /**
     * Format return message.
     *
     * @param  int|\BackedEnum  $code
     * @param  string|null  $message
     * @return string|null
     */
    protected function formatMessage(int|\BackedEnum $code, ?string $message): ?string
    {
        $localizationKey = Config::get('response.localization', 'response');

        return match (true) {
            !$message && Lang::has($localizationKey.$code) => Lang::get($localizationKey),
            default => $message
        };
    }

    /**
     * Format business code
     *
     * @param  int|\BackedEnum  $code
     * @return int
     */
    protected function formatBusinessCode(int|\BackedEnum $code): int
    {
        return enum_exists($code) ? $code->value : $code;
    }

    /**
     * Format http status description.
     *
     * @param  int|\BackedEnum  $code
     * @return string
     */
    protected function formatStatus(int|\BackedEnum $code): string
    {
        $statusCode = $this->formatStatusCode($code);

        return match (true) {
            ($statusCode >= 400 && $statusCode <= 499) => 'error',// client error
            ($statusCode >= 500 && $statusCode <= 599) => 'fail',// service error
            default => 'success'
        };
    }

    /**
     * Http status code.
     *
     * @param  int|\BackedEnum  $code
     * @param  string  $from
     * @return int
     */
    protected function formatStatusCode(int|\BackedEnum $code, string $from = 'success'): int
    {
        $code = match (true) {
            $from === 'fail' => (Config::get('response.error_code') ?: $code),
            default => $this->formatBusinessCode($code)
        };

        return (int) substr($code, 0, 3);
    }

    /**
     * Get JsonResource resource data.
     *
     * @return \Closure
     */
    protected function formatJsonResource(): \Closure
    {
        // vendor/laravel/framework/src/Illuminate/Http/Resources/Json/ResourceResponse.php
        // toResponse
        return function (JsonResource $resource) {
            return array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);
        };
    }

    /**
     * Format paginator data.
     *
     * @param  $collection
     * @return array
     */
    protected function formatMeta($collection): array
    {
        return match (true) {
            $collection instanceof CursorPaginator => [
                'cursor' => [
                    'current' => $collection->cursor()?->encode(),
                    'prev' => $collection->previousCursor()?->encode(),
                    'next' => $collection->nextCursor()?->encode(),
                    'count' => count($collection->items()),
                ],
            ],
            $collection instanceof LengthAwarePaginator => [
                'pagination' => [
                    'count' => $collection->lastItem(),
                    'per_page' => $collection->perPage(),
                    'current_page' => $collection->currentPage(),
                    'total' => $collection->total(),
                    'total_pages' => $collection->lastPage(),
                    'links' => array_filter([
                        'previous' => $collection->previousPageUrl(),
                        'next' => $collection->nextPageUrl(),
                    ]),
                ],
            ],
            $collection instanceof Paginator => [
                'pagination' => [
                    'count' => $collection->lastItem(),
                    'per_page' => $collection->perPage(),
                    'current_page' => $collection->currentPage(),
                    'links' => array_filter([
                        'previous' => $collection->previousPageUrl(),
                        'next' => $collection->nextPageUrl(),
                    ]),
                ],
            ],
            default => [],
        };
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
            if (!Arr::has($data, $key)) {
                continue;
            }

            $show = $config['show'] ?? true;
            $alias = $config['alias'] ?? '';

            if ($alias && $alias !== $key) {
                Arr::set($data, $alias, Arr::get($data, $key));
                $data = Arr::except($data, $key);
                $key = $alias;
            }

            if (!$show) {
                $data = Arr::except($data, $key);
            }
        }

        return $data;
    }
}
