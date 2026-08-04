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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Tests\Support\TestHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses()->beforeEach(function () {
    $this->handler = new TestHandler($this->app);
})->in(__FILE__);

test('prepareJsonResponse dispatches ValidationException to typed formatter', function () {
    Config::set('app.debug', false);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new ValidationException(Validator::make(['name' => ''], ['name' => 'required']))
    );

    expect($response->getStatusCode())->toEqual(422)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 422,
            'message' => 'The name field is required.',
        ]);
});

test('prepareJsonResponse dispatches AuthenticationException to typed formatter', function () {
    Config::set('app.debug', false);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new AuthenticationException('Unauthenticated.')
    );

    expect($response->getStatusCode())->toEqual(401)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 401,
            'message' => 'Unauthenticated.',
        ]);
});

test('prepareJsonResponse dispatches HttpException to generic fallback', function () {
    // Override the package default (which sets message => '') so the trait falls through
    // to the exception's own message rather than the empty-string config.
    Config::set('response.exception.'.NotFoundHttpException::class, []);
    Config::set('app.debug', false);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new NotFoundHttpException('Route not found')
    );

    expect($response->getStatusCode())->toEqual(404)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 404,
            'message' => 'Route not found',
        ]);
});

test('prepareJsonResponse dispatches generic Throwable to generic fallback', function () {
    Config::set('app.debug', false);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new RuntimeException('boom')
    );

    expect($response->getStatusCode())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500,
            'message' => 'Server Error',
        ]);
});

test('prepareJsonResponse honours opt-out (config === false)', function () {
    Config::set('response.exception.'.NotFoundHttpException::class, false);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new NotFoundHttpException('Route not found')
    );

    // parent::prepareJsonResponse returns a JsonResponse with the converted array directly —
    // not the wrapped {status, code, message, data, error} shape.
    expect($response->getStatusCode())->toEqual(404)
        ->and($response->getData(true))->toMatchArray(['message' => 'Route not found']);
});

test('prepareJsonResponse applies 4-key config overrides', function () {
    Config::set('response.exception.'.NotFoundHttpException::class, [
        'message' => 'Custom not-found message',
        'code' => 418,
        'header' => ['X-Custom-Header' => 'foo'],
    ]);

    $response = $this->handler->publicPrepareJsonResponse(
        Request::create('/api/test'),
        new NotFoundHttpException('Original message')
    );

    expect($response->getStatusCode())->toEqual(418)
        ->and($response->headers->get('X-Custom-Header'))->toEqual('foo')
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 418,
            'message' => 'Custom not-found message',
        ]);
});

test('invalidJson delegates to formatValidationException', function () {
    $response = $this->handler->publicInvalidJson(
        Request::create('/api/test'),
        new ValidationException(Validator::make(['email' => 'not-an-email'], ['email' => 'email']))
    );

    expect($response->getStatusCode())->toEqual(422)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 422,
            'message' => 'The email field must be a valid email address.',
        ]);
});

test('invalidJson honours opt-out (config === false)', function () {
    Config::set('response.exception.'.ValidationException::class, false);

    $response = $this->handler->publicInvalidJson(
        Request::create('/api/test'),
        new ValidationException(Validator::make(['email' => 'x'], ['email' => 'email']))
    );

    // parent::invalidJson returns the original Laravel shape — no wrapper.
    expect($response->getStatusCode())->toEqual(422);
});

test('invalidJson applies ValidationException config overrides', function () {
    Config::set('response.exception.'.ValidationException::class, [
        'message' => 'Validation failed',
        'code' => 400,
    ]);

    $response = $this->handler->publicInvalidJson(
        Request::create('/api/test'),
        new ValidationException(Validator::make(['email' => 'x'], ['email' => 'email']))
    );

    expect($response->getStatusCode())->toEqual(400)
        ->and($response->getData(true))->toMatchArray([
            'code' => 400,
            'message' => 'Validation failed',
        ]);
});

test('unauthenticated formats AuthenticationException for JSON requests', function () {
    $request = Request::create('/api/test');
    $request->headers->set('Accept', 'application/json');

    $response = $this->handler->publicUnauthenticated(
        $request,
        new AuthenticationException('Login required.')
    );

    expect($response->getStatusCode())->toEqual(401)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 401,
            'message' => 'Login required.',
        ]);
});

test('unauthenticated applies AuthenticationException config overrides', function () {
    Config::set('response.exception.'.AuthenticationException::class, [
        'message' => '请先登录',
        'code' => 403,
    ]);

    $request = Request::create('/api/test');
    $request->headers->set('Accept', 'application/json');

    $response = $this->handler->publicUnauthenticated(
        $request,
        new AuthenticationException('Login required.')
    );

    expect($response->getStatusCode())->toEqual(403)
        ->and($response->getData(true))->toMatchArray([
            'code' => 403,
            'message' => '请先登录',
        ]);
});

test('unauthenticated falls through to parent for non-JSON requests', function () {
    $this->app['router']->get('login', fn () => 'login-page')->name('login');

    $request = Request::create('/web/test', 'GET');

    $response = $this->handler->publicUnauthenticated(
        $request,
        new AuthenticationException('Login required.')
    );

    // parent::unauthenticated returns a redirect for non-JSON — not our JsonResponse.
    expect($response)->not->toBeInstanceOf(JsonResponse::class);
});

test('unauthenticated honours opt-out (config === false)', function () {
    Config::set('response.exception.'.AuthenticationException::class, false);

    $request = Request::create('/api/test');
    $request->headers->set('Accept', 'application/json');

    $response = $this->handler->publicUnauthenticated(
        $request,
        new AuthenticationException('Login required.')
    );

    // parent::unauthenticated for JSON request still returns a JsonResponse,
    // but it's Laravel's default shape — not our wrapped format.
    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getData(true))->not->toHaveKey('status');
});
