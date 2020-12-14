<?php


namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static JsonResponse|JsonResource accepted($data = null, string $message = '', string $location = '')
 * @method static JsonResponse|JsonResource created($data = null, string $message = '', string $location = '')
 * @method static noContent(string $message = '')
 * @method static JsonResponse|JsonResource success($data = null, string $message = '', $code = 200, array $headers = [], $option = 0)
 * @method static void errorBadRequest(?string $message = '')
 * @method static void errorUnauthorized(string $message = '')
 * @method static void errorForbidden(string $message = '')
 * @method static void errorNotFound(string $message = '')
 * @method static void errorMethodNotAllowed(string $message = '')
 * @method static void errorInternal(string $message = '')
 * @method static void fail(string $message = '', int $code = 500, $errors = null, array $header = [], int $options = 0)
 *
 * @see \Jiannei\Response\Laravel\Response
 */
class Response extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return \Jiannei\Response\Laravel\Response::class;
    }
}