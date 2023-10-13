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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Traits\Macroable;

class Format
{
    use Macroable;

    protected ?array $data = null;
    protected int $statusCode = 200;

    /**
     * Return a new JSON response from the application.
     *
     * @return JsonResponse
     */
    public function response(): JsonResponse
    {
        return new JsonResponse($this->data, $this->statusCode);
    }

    /**
     * Core format.
     *
     * @param  $data
     * @return array|$this
     */
    public function data($data = null): static|array
    {
        if (is_null($data)) {
            return $this->data;
        }

        $bizCode = $data['code'] ?? 200;
        $oriData = $data['data'] ?? null;
        $message = $data['message'] ?? '';
        $error = $data['error'] ?? [];

        return tap($this, function () use ($bizCode, $oriData, $message, $error) {
            $this->statusCode = $this->formatStatusCode($this->formatBusinessCode($bizCode), $oriData);

            $this->data = $this->formatDataFields([
                'status' => $this->formatStatus($this->statusCode),
                'code' => $this->formatBusinessCode($bizCode),
                'message' => $this->formatMessage($this->formatBusinessCode($bizCode), $message),
                'data' => $this->formatData($oriData),
                'error' => $this->formatError($error),
            ]);
        });
    }

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator|AbstractCursorPaginator|Paginator  $resource
     * @return array
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator|Paginator $resource): array
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
     * Format data.
     *
     * @param  $data
     * @return array|object
     */
    protected function formatData($data): array|object
    {
        $formattedData = match (true) {
            $data instanceof ResourceCollection => $this->resourceCollection($data),
            $data instanceof JsonResource => $this->jsonResource($data),
            $data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator => $this->paginator($data),
            $data instanceof Arrayable || (is_object($data) && method_exists($data, 'toArray')) => $data->toArray(),
            default => Arr::wrap($data)
        };

        return $formattedData ?: (object) $data;
    }

    /**
     * Format return message.
     *
     * @param  int  $code
     * @param  string  $message
     * @return string|null
     */
    protected function formatMessage(int $code, string $message = ''): ?string
    {
        $localizationKey = join('.', [Config::get('response.locale', 'enums'), $code]);

        return match (true) {
            ! $message && Lang::has($localizationKey) => Lang::get($localizationKey),
            default => $message
        };
    }

    /**
     * Format business code.
     *
     * @param  int|\BackedEnum  $code
     * @return int
     */
    protected function formatBusinessCode(int|\BackedEnum $code): int
    {
        return $code instanceof \BackedEnum ? $code->value : $code;
    }

    /**
     * Format http status description.
     *
     * @param  int  $statusCode
     * @return string
     */
    protected function formatStatus(int $statusCode): string
    {
        return match (true) {
            ($statusCode >= 400 && $statusCode <= 499) => 'error',// client error
            ($statusCode >= 500 && $statusCode <= 599) => 'fail',// service error
            default => 'success'
        };
    }

    /**
     * Http status code.
     *
     * @param  int  $code
     * @param  $oriData
     * @return int
     */
    protected function formatStatusCode(int $code, $oriData): int
    {
        return (int) substr(is_null($oriData) ? (Config::get('response.error_code') ?: $code) : $code, 0, 3);
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
     * Format error.
     *
     * @param  array  $error
     * @return array|object
     */
    protected function formatError(array $error): object|array
    {
        return Config::get('app.debug') ? $error : (object) [];
    }

    /**
     * Format response data fields.
     *
     * @param  array  $data
     * @return array
     */
    protected function formatDataFields(array $data): array
    {
        $formatConfig = Config::get('response.format.config', []);

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
