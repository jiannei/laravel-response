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

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $data = null;

    protected int $statusCode = 200;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = []) {}

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
     *
     * @return array<string, mixed>|null
     */
    public function get(): ?array
    {
        return $this->data;
    }

    /**
     * Core format.
     */
    public function data(mixed $data = null, string $message = '', int|\BackedEnum $code = 200, mixed $error = null): static
    {
        $bizCode = $this->formatBusinessCode($code);

        $this->data = $this->formatDataFields([
            'status' => $this->formatStatus($bizCode),
            'code' => $bizCode,
            'message' => $this->formatMessage($bizCode, $message),
            'data' => $this->formatData($data),
            'error' => $this->formatError(null),
        ]);

        $this->statusCode = $this->formatStatusCode($bizCode);

        return $this;
    }

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator<int|string, mixed>|AbstractCursorPaginator<int|string, mixed>|Paginator<int|string, mixed>  $resource
     * @return array<string, mixed>
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator|Paginator $resource): array
    {
        $data = method_exists($resource, 'toArray') ? $resource->toArray()['data'] : $resource->items();

        return [
            'data' => $data,
            'meta' => $this->formatMeta($resource),
        ];
    }

    /**
     * Format collection resource data.
     *
     * @return array<string, mixed>
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
     *
     * @return array<string, mixed>
     */
    public function jsonResource(JsonResource $resource): array
    {
        return value($this->formatJsonResource(), $resource);
    }

    /**
     * Format data.
     *
     * @return array<string, mixed>|object
     */
    protected function formatData(mixed $data): array|object
    {
        return match (true) {
            $data instanceof ResourceCollection => $this->resourceCollection($data),
            $data instanceof JsonResource => $this->jsonResource($data),
            $data instanceof AbstractPaginator, $data instanceof AbstractCursorPaginator => $this->paginator($data),
            $data instanceof Arrayable, (is_object($data) && method_exists($data, 'toArray')) => $data->toArray(),
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
            ! $message && Lang::has($localizationKey) => Lang::get($localizationKey),
            default => $message
        };
    }

    /**
     * Format business code.
     */
    protected function formatBusinessCode(int|\BackedEnum $code): int
    {
        return $code instanceof \BackedEnum ? (int) $code->value : $code;
    }

    /**
     * Format http status description.
     */
    protected function formatStatus(int $bizCode): string
    {
        $statusCode = (int) substr((string) $bizCode, 0, 3);

        return match (true) {
            ($statusCode >= 400 && $statusCode <= 499) => 'error',// client error
            ($statusCode >= 500 && $statusCode <= 599) => 'fail',// service error
            default => 'success'
        };
    }

    /**
     * Http status code.
     */
    protected function formatStatusCode(int $code): int
    {
        $errorCode = Config::get('response.error_code');
        $codeToUse = is_numeric($errorCode) ? $errorCode : $code;

        return (int) substr((string) $codeToUse, 0, 3);
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
     *
     * @return array<string, mixed>
     */
    protected function formatMeta(mixed $collection): array
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
     *
     * @param  array<string, mixed>|null  $error
     * @return object|array<string, mixed>
     */
    protected function formatError(?array $error): object|array
    {
        return $error ?: (object) [];
    }

    /**
     * Format response data fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function formatDataFields(array $data): array
    {
        foreach ($this->config as $key => $config) {
            if (! is_string($key) && ! is_int($key) || ! Arr::has($data, $key)) {
                continue;
            }

            $show = is_array($config) ? ($config['show'] ?? true) : true;
            $alias = is_array($config) ? ($config['alias'] ?? '') : '';

            if (! $show) {
                unset($data[$key]);

                continue;
            }

            if ($alias && $alias !== $key) {
                $data[$alias] = Arr::get($data, $key);
                unset($data[$key]);
            }
        }

        return $data;
    }
}
