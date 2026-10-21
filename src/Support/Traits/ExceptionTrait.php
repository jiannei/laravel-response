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
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Support\Exceptions\ExceptionResponse;
use Jiannei\Response\Laravel\Support\Format;
use Throwable;

/**
 * @phpstan-require-extends Handler
 */
trait ExceptionTrait
{
    /**
     * Custom normal exception response.
     *
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        return $this->exceptionResponse()->render($e)
            ?? parent::prepareJsonResponse($request, $e);
    }

    /**
     * Custom failed validation response for Laravel.
     *
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return $this->exceptionResponse()->render($exception)
            ?? parent::invalidJson($request, $exception);
    }

    /**
     * Custom failed authentication response for Laravel.
     *
     * @return RedirectResponse|JsonResponse|Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if (! $request->expectsJson()) {
            return parent::unauthenticated($request, $exception);
        }

        return $this->exceptionResponse()->render($exception)
            ?? parent::unauthenticated($request, $exception);
    }

    /**
     * Build the exception response module for the active application.
     */
    protected function exceptionResponse(): ExceptionResponse
    {
        /** @var ResponseConfig $config */
        $config = app(ResponseConfig::class);

        /** @var Format $format */
        $format = app(Format::class);

        return new ExceptionResponse(
            $config,
            $format,
            fn (Throwable $e): array => $this->convertExceptionToArray($e),
        );
    }
}
