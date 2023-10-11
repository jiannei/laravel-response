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
use Illuminate\Support\Traits\Macroable;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
use Spatie\Fractal\Fractal;

class Format
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
     * @param  JsonResource|array|mixed  $data
     * @param  string|null  $message
     * @param  int  $code
     * @param  null  $errors
     * @return array
     */
    public function data($data, ?string $message, int $code, $errors = null): array
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
     * @return array
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator $resource): array
    {
        $fractal = fractal()->collection($resource, function ($item) {
            return $item->toArray();
        })->serializeWith(DataArraySerializer::class);

        return tap($fractal, $this->formatCollection($resource))->toArray();
    }

    /**
     * Format collection resource data.
     *
     * @param  ResourceCollection  $collection
     * @return array
     */
    public function resourceCollection(ResourceCollection $collection): array
    {
        $fractal = fractal()->collection($collection->resource, function (JsonResource $resource) {
            return array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);
        })->serializeWith(DataArraySerializer::class);

        return tap($fractal, $this->formatCollection($collection->resource))->toArray();
    }

    /**
     * Format JsonResource Data.
     *
     * @param  JsonResource  $resource
     * @return array
     */
    public function jsonResource(JsonResource $resource): array
    {
        $data = array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);

        return fractal()->item($data, fn () => $data)->serializeWith(ArraySerializer::class)->toArray();
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

    protected function formatCollection($collection): \Closure
    {
        return function (Fractal $item) use ($collection) {
            return match (true) {
                $collection instanceof CursorPaginator => $item->withCursor(new Cursor(
                    $collection->cursor()?->encode(),
                    $collection->previousCursor()?->encode(),
                    $collection->nextCursor()?->encode(),
                    count($collection->items())
                )),
                $collection instanceof LengthAwarePaginator => $item->paginateWith(new IlluminatePaginatorAdapter($collection)),
                $collection instanceof Paginator => $item->addMeta([
                    'pagination' => [
                        'count' => count($collection->items()),
                        'per_page' => $collection->perPage(),
                        'current_page' => $collection->currentPage(),
                        'links' => array_filter([
                            'previous' => $paginated['prev_page_url'] ?? '',
                            'next' => $paginated['next_page_url'] ?? '',
                        ]),
                    ],
                ]),
                default => $item,
            };
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
