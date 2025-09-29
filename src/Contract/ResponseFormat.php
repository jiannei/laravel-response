<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Contract;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

interface ResponseFormat
{
    public function response(): JsonResponse;

    /**
     * Get formatted data.
     *
     * @return array<string, mixed>|null
     */
    public function get(): ?array;

    /**
     * Format data structures.
     *
     * @return $this
     */
    public function data(mixed $data = null, string $message = '', int|\BackedEnum $code = 200, mixed $error = null): static;

    /**
     * Format paginator data.
     *
     * @param  AbstractPaginator<int|string, mixed>|AbstractCursorPaginator<int|string, mixed>|Paginator<int|string, mixed>  $resource
     * @return array<string, mixed>
     */
    public function paginator(AbstractPaginator|AbstractCursorPaginator|Paginator $resource): array;

    /**
     * Format collection resource data.
     *
     * @return array<string, mixed>
     */
    public function resourceCollection(ResourceCollection $collection): array;

    /**
     * Format JsonResource Data.
     *
     * @return array<string, mixed>
     */
    public function jsonResource(JsonResource $resource): array;
}
