<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Exceptions;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Support\Format;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionResponse
{
    protected const DEFAULT_OPTIONS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

    /**
     * @param  Closure(Throwable): array<string, mixed>  $exceptionToArray
     */
    public function __construct(
        protected ResponseConfig $config,
        protected Format $format,
        protected Closure $exceptionToArray,
    ) {}

    /**
     * Render an exception, or return null when the exception is opted out.
     */
    public function render(Throwable $e): ?JsonResponse
    {
        $rule = $this->config->exceptionRule(get_class($e));

        if ($rule === false) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => $this->validation($e, $rule),
            $e instanceof AuthenticationException => $this->authentication($e, $rule),
            default => $this->generic($e, $rule),
        };
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    protected function validation(ValidationException $e, array $rule): JsonResponse
    {
        return $this->build(
            code: (int) ($rule['code'] ?? 422),
            message: (string) ($rule['message'] ?? $e->validator->errors()->first()),
            error: $rule['error'] ?? $e->errors(),
            header: $this->header($rule),
            options: (int) ($rule['options'] ?? self::DEFAULT_OPTIONS),
        );
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    protected function authentication(AuthenticationException $e, array $rule): JsonResponse
    {
        return $this->build(
            code: (int) ($rule['code'] ?? 401),
            message: (string) ($rule['message'] ?? $e->getMessage()),
            error: $rule['error'] ?? null,
            header: $this->header($rule),
            options: (int) ($rule['options'] ?? self::DEFAULT_OPTIONS),
        );
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    protected function generic(Throwable $e, array $rule): JsonResponse
    {
        $http = $e instanceof HttpExceptionInterface;

        return $this->build(
            code: (int) ($rule['code'] ?? ($http ? $e->getStatusCode() : 500)),
            message: (string) ($rule['message'] ?? ($http ? $e->getMessage() : 'Server Error')),
            error: $rule['error'] ?? ($this->exceptionToArray)($e),
            header: $this->header($rule, $http ? $e->getHeaders() : []),
            options: (int) ($rule['options'] ?? self::DEFAULT_OPTIONS),
        );
    }

    /**
     * @param  array<string, mixed>  $rule
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    protected function header(array $rule, array $default = []): array
    {
        $header = $rule['header'] ?? $default;

        return is_array($header) ? $header : $default;
    }

    /**
     * @param  array<string, mixed>  $header
     */
    protected function build(
        int $code,
        string $message = '',
        mixed $error = null,
        array $header = [],
        int $options = self::DEFAULT_OPTIONS,
    ): JsonResponse {
        $error = is_array($error) || is_object($error) ? $error : null;

        return $this->format->make(message: $message, code: $code, error: $error)
            ->withHeaders($header)
            ->setEncodingOptions($options);
    }
}
