<?php

namespace Jiannei\Response\Laravel\Support;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Macroable;

class Format
{
    use Macroable;

    /**
     * Format return data structure.
     *
     * @param  JsonResource|array|null  $data
     * @param $message
     * @param $code
     * @param  null  $errors
     * @return array
     */
    public function data($data, $message, $code, $errors = null): array
    {
        if (! $message && class_exists($enumClass = Config::get('response.enum'))) {
            $message = $enumClass::fromValue($code)->description;
        }

        return $this->formatDataFields([
            'status' => $this->formatStatus($code),
            'code' => $code,
            'message' => $message,
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
        ], Config::get('response.format.fields', []));
    }

    /**
     * Http status code.
     *
     * @param $code
     * @return int
     */
    public function statusCode($code): int
    {
        return (int) substr($code, 0, 3);
    }


    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return mixed
     */
    public function paginator($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        $paginated = $resource->toArray();

        $paginationInformation = $this->formatPaginatedData($paginated);

        $data = array_merge_recursive(['data' => $paginated['data']], $paginationInformation);

        return $this->data($data, $message, $code);
    }

    /**
     * Format collection resource data.
     *
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return mixed
     */
    public function resourceCollection($resource, string $message = '', int $code = 200, array $headers = [], int $option = 0)
    {
        $data = array_merge_recursive(['data' => $resource->resolve(request())], $resource->with(request()), $resource->additional);
        if ($resource->resource instanceof AbstractPaginator) {
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
     * @return mixed
     */
    public function jsonResource($resource, string $message = '', $code = 200, array $headers = [], $option = 0)
    {
        $resourceData = array_merge_recursive($resource->resolve(request()), $resource->with(request()), $resource->additional);

        return $this->data($resourceData, $message, $code);
    }

    /**
     * Format http status description.
     *
     * @param  int  $code
     * @return string
     */
    protected function formatStatus(int $code): string
    {
        $statusCode = $this->statusCode($code);
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
            $map = $fieldConfig['map'] ?? null;
            unset($responseData[$field]);

            if ($show) {
                $responseData[$alias] = $map[$value] ?? $value;
            }
        }

        return $responseData;
    }
}