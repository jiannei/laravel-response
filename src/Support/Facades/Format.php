<?php

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 *
 * @method static JsonResponse response($data = [], int $status = 200, array $headers = [], int $options = 0)
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