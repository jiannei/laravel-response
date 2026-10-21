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

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;

trait ExceptionTrait
{
    /**
     * Custom Normal Exception response.
     *
     * Entry point invoked by Laravel for any Throwable during JSON rendering.
     * Dispatches to the appropriate typed formatter or the generic fallback.
     *
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $config = $this->resolveExceptionConfig($e);

        if ($config === false) {
            return parent::prepareJsonResponse($request, $e);
        }

        return match (true) {
            $e instanceof ValidationException => $this->formatValidationException($e, $config),
            $e instanceof AuthenticationException => $this->formatAuthenticationException($e, $config),
            default => $this->formatException(
                code: $config['code'] ?? ($this->isHttpException($e) && method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500),
                message: $config['message'] ?? ($this->isHttpException($e) ? $e->getMessage() : 'Server Error'),
                error: $this->convertExceptionToArray($e),
                header: $config['header'] ?? ($this->isHttpException($e) && method_exists($e, 'getHeaders') ? $e->getHeaders() : []),
                options: $config['options'] ?? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ),
        };
    }

    /**
     * Custom Failed Validation Response for Laravel.
     *
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $config = $this->resolveExceptionConfig($exception);

        if ($config === false) {
            return parent::invalidJson($request, $exception);
        }

        return $this->formatValidationException($exception, $config);
    }

    /**
     * Custom Failed Authentication Response for Laravel.
     *
     * @return RedirectResponse|JsonResponse|\Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $config = $this->resolveExceptionConfig($exception);

        if ($config === false || ! $request->expectsJson()) {
            return parent::unauthenticated($request, $exception);
        }

        return $this->formatAuthenticationException($exception, $config);
    }

    /**
     * Format a ValidationException to a JSON response using the unified 4-key config.
     *
     * Config keys: message / code / header / options. Missing keys fall back to
     * validation-specific defaults (code=422, first validator error as message).
     *
     * @param  array<string, mixed>  $config
     */
    protected function formatValidationException(ValidationException $exception, array $config): JsonResponse
    {
        return $this->formatException(
            code: $config['code'] ?? 422,
            message: $config['message'] ?? (string) $exception->validator->errors()->first(),
            error: $exception->errors(),
            header: $config['header'] ?? [],
            options: $config['options'] ?? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }

    /**
     * Format an AuthenticationException to a JSON response using the unified 4-key config.
     *
     * Config keys: message / code / header / options. Missing keys fall back to
     * auth-specific defaults (code=401, exception message).
     *
     * @param  array<string, mixed>  $config
     */
    protected function formatAuthenticationException(AuthenticationException $exception, array $config): JsonResponse
    {
        return $this->formatException(
            code: $config['code'] ?? 401,
            message: $config['message'] ?? $exception->getMessage(),
            error: $config['error'] ?? null,
            header: $config['header'] ?? [],
            options: $config['options'] ?? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }

    /**
     * Final JsonResponse builder for exception responses.
     *
     * All typed formatters converge here. Each caller is responsible for
     * extracting the appropriate message / code / error / header / options
     * for its exception type — this method only assembles the response.
     *
     * @param  array<string, mixed>  $header
     */
    protected function formatException(
        int $code,
        string $message = '',
        mixed $error = null,
        array $header = [],
        int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
    ): JsonResponse {
        return Response::fail($message, $code, $error)
            ->withHeaders($header)
            ->setEncodingOptions($options);
    }

    /**
     * Resolve the config block for an exception with unified opt-out semantics.
     *
     * Returns:
     *  - false: explicit opt-out (response.exception.<FQCN> === false) — caller
     *    must fall through to parent::* behaviour.
     *  - array: the configured block (empty array if no block exists).
     *
     * @return array<string, mixed>|false
     */
    private function resolveExceptionConfig(Throwable|string $e): array|false
    {
        $class = $e instanceof Throwable ? get_class($e) : $e;
        $config = Config::get('response.exception.'.$class);

        return $config === false ? false : ($config ?? []);
    }
}
