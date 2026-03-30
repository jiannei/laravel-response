<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Traits;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;

trait ExceptionTrait
{
    /**
     * The response builder callback.
     *
     * @var callable|null
     */
    protected static $responseBuilder;

    /**
     * Custom Normal Exception response.
     *
     * @param  Request  $request
     * @param  Throwable|Exception  $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, $e)
    {
        $exceptionConfig = Config::get('response.exception.'.get_class($e));

        if ($exceptionConfig === false) {
            return parent::prepareJsonResponse($request, $e);
        }

        $isHttpException = $this->isHttpException($e);
        $message = $exceptionConfig['message'] ?? ($isHttpException ? $e->getMessage() : 'Server Error');
        $code = $exceptionConfig['code'] ?? ($isHttpException && method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        $header = $exceptionConfig['header'] ?? ($isHttpException && method_exists($e, 'getHeaders') ? $e->getHeaders() : []);
        $options = $exceptionConfig['options'] ?? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return Response::fail($message, $code, $this->convertExceptionToArray($e))
            ->withHeaders($header)
            ->setEncodingOptions($options);
    }

    /**
     * Custom Failed Validation Response for Lumen.
     *
     * @param  array<string, mixed>  $errors
     * @return mixed
     *
     * @throws HttpResponseException
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if (isset(static::$responseBuilder)) {
            return (static::$responseBuilder)($request, $errors);
        }

        $message = $this->extractStringFromMessage(Arr::first($errors, null, ''));
        $code = Config::get('response.exception.'.ValidationException::class.'.code', 422);

        return Response::fail($message, is_numeric($code) ? (int) $code : 422, $errors);
    }

    /**
     * Custom Failed Validation Response for Laravel.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $code = Config::get('response.exception.'.ValidationException::class.'.code', 422);

        return Response::fail(
            (string) $exception->validator->errors()->first(),
            is_numeric($code) ? (int) $code : 422,
            $exception->errors()
        );
    }

    /**
     * Extract string message from mixed message format.
     */
    private function extractStringFromMessage(mixed $message): string
    {
        return match (true) {
            is_string($message) => $message,
            is_array($message) => is_string($first = Arr::first($message)) ? $first : '',
            default => (string) $message,
        };
    }

    /**
     * Custom Failed Authentication Response for Laravel.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|JsonResponse|\Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $exceptionConfig = Config::get('response.exception.'.AuthenticationException::class);

        if ($exceptionConfig !== false && $request->expectsJson()) {
            return Response::errorUnauthorized($exceptionConfig['message'] ?? $exception->getMessage());
        }

        return parent::unauthenticated($request, $exception);
    }
}
