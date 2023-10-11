<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static JsonResponse response($data = null, string $message = '', int $code = 200, $errors = null, array $headers = [], int $option = 0, string $from = 'success')
 * @method static array data($data, ?string $message, int $code, $errors = null)
 * @method static array paginator(AbstractPaginator|AbstractCursorPaginator $resource)
 * @method static array resourceCollection(ResourceCollection $collection)
 * @method static array jsonResource(JsonResource $resource)
 *
 * @see \Jiannei\Response\Laravel\Support\Format
 */
class Format extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return config('response.format.class', \Jiannei\Response\Laravel\Support\Format::class);
    }
}
