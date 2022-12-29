<?php

namespace Jiannei\Response\Laravel\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

interface Format
{
    /**
     * Format return data structure.
     *
     * @param  array|null  $data
     * @param  string|null  $message
     * @param int $code
     * @param  null  $errors
     * @return array
     */
    public function data(?array $data, ?string $message, int $code, $errors = null): array;

    /**
     * Http status code.
     *
     * @param $code
     * @return int
     */
    public function statusCode($code): int;

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator  $resource
     * @param  string  $message
     * @param  int  $code
     * @param  array  $headers
     * @param  int  $option
     * @return array
     */
    public function paginator(AbstractPaginator $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0):array;

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
    public function resourceCollection(ResourceCollection $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array;

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
    public function jsonResource(JsonResource $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array;
}