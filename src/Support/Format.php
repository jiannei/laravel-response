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
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Traits\Macroable;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;

class Format
{
    use Macroable;

    public function __construct(protected ResponseConfig $config) {}

    /**
     * Build a formatted JSON response.
     *
     * @param  array<string, mixed>|object|null  $error
     */
    public function make(
        mixed $data = null,
        string $message = '',
        int|\BackedEnum $code = 200,
        array|object|null $error = null,
    ): JsonResponse {
        $bizCode = $this->formatBusinessCode($code);
        $status = $this->formatStatus($bizCode);

        $payload = $this->formatDataFields([
            'status' => $status,
            'code' => $bizCode,
            'message' => $this->formatMessage($bizCode, $message),
            'data' => $this->formatData($data),
            'error' => $this->formatError($error),
        ]);

        return new JsonResponse($payload, $this->formatStatusCode($bizCode, $status));
    }

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator<int|string, mixed>|AbstractCursorPaginator<int|string, mixed>|Paginator<int|string, mixed>  $resource
     * @return array<string, mixed>
     */
    protected function paginator(AbstractPaginator|AbstractCursorPaginator|Paginator $resource): array
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
    protected function resourceCollection(ResourceCollection $collection): array
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
    protected function jsonResource(JsonResource $resource): array
    {
        return array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);
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
            $data instanceof AbstractPaginator,
            $data instanceof AbstractCursorPaginator,
            $data instanceof Paginator => $this->paginator($data),
            $data instanceof Arrayable, (is_object($data) && method_exists($data, 'toArray')) => $data->toArray(),
            empty($data) => (object) $data,
            default => Arr::wrap($data),
        };
    }

    /**
     * Format return message.
     */
    protected function formatMessage(int $code, string $message = ''): ?string
    {
        if ($message !== '') {
            return $message;
        }

        $localizationKey = implode('.', [$this->config->locale(), $code]);
        $localized = Lang::has($localizationKey) ? Lang::get($localizationKey) : null;

        return is_string($localized) ? $localized : null;
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
            ($statusCode >= 400 && $statusCode <= 499) => 'error', // client error
            ($statusCode >= 500 && $statusCode <= 599) => 'fail', // service error
            default => 'success',
        };
    }

    /**
     * Http status code.
     *
     * The configured error code only overrides failed responses; successful
     * responses keep the first three digits of their business code.
     */
    protected function formatStatusCode(int $bizCode, string $status): int
    {
        $errorCode = $this->config->errorCodePolicy();

        if ($errorCode !== false && $status !== 'success') {
            return (int) substr((string) $errorCode, 0, 3);
        }

        return (int) substr((string) $bizCode, 0, 3);
    }

    /**
     * Format paginator meta.
     *
     * @return array<string, mixed>
     */
    protected function formatMeta(mixed $collection): array
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
     * @param  array<string, mixed>|object|null  $error
     * @return object|array<string, mixed>
     */
    protected function formatError(array|object|null $error): object|array
    {
        return $error === null || $error === [] ? (object) [] : $error;
    }

    /**
     * Apply the configured display and alias rules to the response envelope.
     *
     * Rules are applied deepest-first so nested paths such as `data.data` are
     * still reachable after their parent alias is applied.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function formatDataFields(array $data): array
    {
        $fields = $this->config->fields();

        uksort($fields, static function ($a, $b): int {
            return substr_count((string) $b, '.') <=> substr_count((string) $a, '.');
        });

        foreach ($fields as $key => $fieldConfig) {
            if (! is_string($key) || ! Arr::has($data, $key) || ! is_array($fieldConfig)) {
                continue;
            }

            $show = (bool) ($fieldConfig['show'] ?? true);
            $alias = $fieldConfig['alias'] ?? '';

            if (! $show) {
                Arr::forget($data, $key);

                continue;
            }

            if (is_string($alias) && $alias !== '' && $alias !== $key) {
                Arr::set($data, $alias, Arr::get($data, $key));
                Arr::forget($data, $key);
            }
        }

        return $data;
    }
}
