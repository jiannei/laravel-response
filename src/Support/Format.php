<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
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
use Jiannei\Response\Laravel\Contract\ResponseFormat;

class Format implements ResponseFormat
{
    use Macroable;

    protected ?array $data = null;

    protected int $statusCode = 200;

    public function __construct(protected array $config = [])
    {
    }

    /**
     * Return a new JSON response from the application.
     */
    public function response(): JsonResponse
    {
        return tap(new JsonResponse($this->data, $this->statusCode), function () {
            $this->data = null;
            $this->statusCode = 200;
        });
    }

    /**
     * Get formatted data.
     */
    public function get(): ?array
    {
        return $this->data;
    }

    /**
     * Core format.
     *
     * @param null $data
     * @param null $error
     *
     * @return Format
     */
    public function data(mixed $data = null, string $message = '', int|\BackedEnum $code = 200, $error = null): static
    {
        return tap($this, function () use ($data, $message, $code, $error) {
            $this->statusCode = $this->formatStatusCode($this->formatBusinessCode($code), $data);

            $this->data = $this->formatDataFields([
                'status' => $this->formatStatus($this->statusCode),
                'code' => $this->formatBusinessCode($code),
                'message' => $this->formatMessage($this->formatBusinessCode($code), $message),
                'data' => $this->formatData($data),
                'error' => $this->formatError($error),
            ]);
        });
    }

    /**
     * Format paginator data.
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
     */
    public function resourceCollection(ResourceCollection $collection): array
    {
        return [
            'data' => $collection->resolve(),
            'meta' => array_merge_recursive($this->formatMeta($collection->resource), $collection->with(request()), $collection->additional),
        ];
    }

    /**
     * Format JsonResource Data.
     */
    public function jsonResource(JsonResource $resource): array
    {
        return value($this->formatJsonResource(), $resource);
    }

    /**
     * Format data.
     */
    protected function formatData($data): array|object
    {
        return match (true) {
            $data instanceof ResourceCollection => $this->resourceCollection($data),
            $data instanceof JsonResource => $this->jsonResource($data),
            $data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator => $this->paginator($data),
            $data instanceof Arrayable || (is_object($data) && method_exists($data, 'toArray')) => $data->toArray(),
            empty($data) => (object) $data,
            default => Arr::wrap($data)
        };
    }

    /**
     * Format return message.
     */
    protected function formatMessage(int $code, string $message = ''): ?string
    {
        $localizationKey = implode('.', [Config::get('response.locale', 'enums'), $code]);

        return match (true) {
            !$message && Lang::has($localizationKey) => Lang::get($localizationKey),
            default => $message
        };
    }

    /**
     * Format business code.
     */
    protected function formatBusinessCode(int|\BackedEnum $code): int
    {
        return $code instanceof \BackedEnum ? $code->value : $code;
    }

    /**
     * Format http status description.
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
     */
    protected function formatStatusCode(int $code, $oriData): int
    {
        return (int) substr(is_null($oriData) ? (Config::get('response.error_code') ?: $code) : $code, 0, 3);
    }

    /**
     * Get JsonResource resource data.
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
     */
    protected function formatMeta($collection): array
    {
        // vendor/laravel/framework/src/Illuminate/Http/Resources/Json/PaginatedResourceResponse.php
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
     */
    protected function formatError(?array $error): object|array
    {
        return $error ?: (object) [];
    }

    /**
     * Format response data fields.
     */
    protected function formatDataFields(array $data): array
    {
        return tap($data, function (&$item) {
            foreach ($this->config as $key => $config) {
                if (!Arr::has($item, $key)) {
                    continue;
                }

                $show = $config['show'] ?? true;
                $alias = $config['alias'] ?? '';

                if ($alias && $alias !== $key) {
                    Arr::set($item, $alias, Arr::get($item, $key));
                    $item = Arr::except($item, $key);
                    $key = $alias;
                }

                if (!$show) {
                    $item = Arr::except($item, $key);
                }
            }
        });
    }
}
