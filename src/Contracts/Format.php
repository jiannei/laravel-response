<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

interface Format
{
    /**
     * Format return data structure.
     *
     * @param  array|null  $data
     * @param  string|null  $message
     * @param  int  $code
     * @param  null  $errors
     * @return array
     */
    public function data(?array $data, ?string $message, int $code, $errors = null): array;

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
    public function paginator(AbstractPaginator|AbstractCursorPaginator $resource, string $message = '', int $code = 200, array $headers = [], int $option = 0): array;

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
