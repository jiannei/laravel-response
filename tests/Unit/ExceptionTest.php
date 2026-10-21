<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Support\Exceptions\ExceptionResponse;
use Jiannei\Response\Laravel\Support\Format;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function exceptionModule(array $overrides = [], ?Closure $converter = null): ExceptionResponse
{
    $config = new ResponseConfig(array_replace_recursive([
        'locale' => 'enums.'.ResponseEnum::class,
        'error_code' => false,
        'exception' => [
            ValidationException::class => ['code' => 422],
            AuthenticationException::class => [],
            NotFoundHttpException::class => [],
        ],
        'format' => ['class' => Format::class, 'config' => []],
    ], $overrides));

    $converter ??= fn (Throwable $e): array => ['debug' => $e->getMessage()];

    return new ExceptionResponse($config, new Format($config), $converter);
}

function rendered(ExceptionResponse $module, Throwable $e): JsonResponse
{
    $response = $module->render($e);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    assert($response instanceof JsonResponse);

    return $response;
}

test('validation exceptions use the validation defaults', function () {
    $response = rendered(exceptionModule(), new ValidationException(
        Validator::make(['name' => ''], ['name' => 'required'])
    ));

    expect($response->getStatusCode())->toEqual(422)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 422,
            'message' => 'The name field is required.',
            'error' => ['name' => ['The name field is required.']],
        ]);
});

test('authentication exceptions use the auth defaults', function () {
    $response = rendered(exceptionModule(), new AuthenticationException('Unauthenticated.'));

    expect($response->getStatusCode())->toEqual(401)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 401,
            'message' => 'Unauthenticated.',
        ]);
});

test('http exceptions use their status and message', function () {
    $response = rendered(exceptionModule(), new NotFoundHttpException('Route not found'));

    expect($response->getStatusCode())->toEqual(404)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 404,
            'message' => 'Route not found',
            'error' => ['debug' => 'Route not found'],
        ]);
});

test('generic throwables use the server error fallback', function () {
    $response = rendered(exceptionModule(), new RuntimeException('boom'));

    expect($response->getStatusCode())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500,
            'message' => 'Server Error',
            'error' => ['debug' => 'boom'],
        ]);
});

test('an exception rule set to false falls through', function () {
    $module = exceptionModule([
        'exception' => [NotFoundHttpException::class => false],
    ]);

    expect($module->render(new NotFoundHttpException('Route not found')))->toBeNull();
});

test('exception rules override code, message, header and options', function () {
    $module = exceptionModule([
        'exception' => [
            NotFoundHttpException::class => [
                'message' => 'Custom not-found message',
                'code' => 418,
                'header' => ['X-Custom-Header' => 'foo'],
                'options' => JSON_UNESCAPED_SLASHES,
            ],
        ],
    ]);

    $response = rendered($module, new NotFoundHttpException('Original message'));

    expect($response->getStatusCode())->toEqual(418)
        ->and($response->headers->get('X-Custom-Header'))->toEqual('foo')
        ->and($response->getEncodingOptions())->toEqual(JSON_UNESCAPED_SLASHES)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 418,
            'message' => 'Custom not-found message',
        ]);
});

test('validation rules can override the error payload', function () {
    $module = exceptionModule([
        'exception' => [
            ValidationException::class => [
                'message' => 'Validation failed',
                'error' => ['custom' => ['error']],
            ],
        ],
    ]);

    $response = rendered($module, new ValidationException(
        Validator::make(['email' => 'x'], ['email' => 'email'])
    ));

    expect($response->getData(true))->toMatchArray([
        'message' => 'Validation failed',
        'error' => ['custom' => ['error']],
    ]);
});
