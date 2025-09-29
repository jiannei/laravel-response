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
        // 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
        // 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;
        $exceptionConfig = Config::get('response.exception.'.get_class($e));

        if ($exceptionConfig === false) {
            return parent::prepareJsonResponse($request, $e);
        }

        $isHttpException = $this->isHttpException($e);

        $message = is_array($exceptionConfig) && isset($exceptionConfig['message']) && is_scalar($exceptionConfig['message']) ? (string) $exceptionConfig['message'] : ($isHttpException ? $e->getMessage() : 'Server Error');
        $code = is_array($exceptionConfig) && isset($exceptionConfig['code']) ? $exceptionConfig['code'] : ($isHttpException && method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        $header = is_array($exceptionConfig) && isset($exceptionConfig['header']) ? $exceptionConfig['header'] : ($isHttpException && method_exists($e, 'getHeaders') ? $e->getHeaders() : []);
        $options = is_array($exceptionConfig) && isset($exceptionConfig['options']) ? $exceptionConfig['options'] : (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

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

        $firstMessage = Arr::first($errors, null, '');
        $message = $this->extractStringFromMessage($firstMessage);

        $exceptionConfig = Config::get('response.exception');
        $code = is_array($exceptionConfig) ? Arr::get($exceptionConfig, ValidationException::class.'.code', 422) : 422;
        $codeValue = is_numeric($code) ? (int) $code : 422;

        return Response::fail($message, $codeValue, $errors);
    }

    /**
     * Custom Failed Validation Response for Laravel.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $exceptionConfig = Config::get('response.exception.'.ValidationException::class);

        if ($exceptionConfig !== false) {
            $firstError = $exception->validator->errors()->first();
            $message = (string) $firstError;
            $code = is_array($exceptionConfig) ? Arr::get($exceptionConfig, 'code', 422) : 422;
            $codeValue = is_numeric($code) ? (int) $code : 422;

            return Response::fail($message, $codeValue, $exception->errors());
        }

        return parent::invalidJson($request, $exception);
    }

    /**
     * Extract string message from mixed message format.
     */
    private function extractStringFromMessage(mixed $message): string
    {
        if (is_string($message)) {
            return $message;
        }

        if (is_array($message)) {
            $firstElement = Arr::first($message);

            return is_scalar($firstElement) ? (string) $firstElement : '';
        }

        return is_scalar($message) ? (string) $message : '';
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

        return $exceptionConfig !== false && $request->expectsJson()
            ? Response::errorUnauthorized(is_array($exceptionConfig) && is_string($exceptionConfig['message'] ?? null) ? $exceptionConfig['message'] : $exception->getMessage())
            : parent::unauthenticated($request, $exception);
    }
}
