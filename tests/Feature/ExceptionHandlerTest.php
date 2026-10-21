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
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('handler renders validation exceptions through the module', function () {
    Route::get('/validation', function () {
        throw ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);
    });

    $this->getJson('/validation')
        ->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('code', 422)
        ->assertJsonPath('message', 'The email field is required.')
        ->assertJsonPath('error.email.0', 'The email field is required.');
});

test('handler renders authentication exceptions through the module', function () {
    Route::get('/auth', function () {
        throw new AuthenticationException('Login required.');
    });

    $this->getJson('/auth')
        ->assertStatus(401)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('code', 401)
        ->assertJsonPath('message', 'Login required.');
});

test('handler renders http exceptions through the module', function () {
    $this->useResponseConfig([
        'exception' => [
            NotFoundHttpException::class => ['message' => 'Not found'],
        ],
    ]);

    Route::get('/missing', fn () => abort(404, 'Original message'));

    $this->getJson('/missing')
        ->assertStatus(404)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('code', 404)
        ->assertJsonPath('message', 'Not found');
});

test('handler renders generic throwables through the module', function () {
    Route::get('/boom', function () {
        throw new RuntimeException('boom');
    });

    $this->getJson('/boom')
        ->assertStatus(500)
        ->assertJsonPath('status', 'fail')
        ->assertJsonPath('code', 500)
        ->assertJsonPath('message', 'Server Error');
});

test('handler falls through when the exception is opted out', function () {
    $this->useResponseConfig([
        'exception' => [
            NotFoundHttpException::class => false,
        ],
    ]);

    Route::get('/missing-opt-out', fn () => abort(404, 'Original message'));

    $this->getJson('/missing-opt-out')
        ->assertStatus(404)
        ->assertJsonMissingPath('status')
        ->assertJsonPath('message', 'Original message');
});

test('validation opt-out maps to the parent invalidJson hook', function () {
    $this->useResponseConfig([
        'exception' => [
            ValidationException::class => false,
        ],
    ]);

    Route::get('/validation-opt-out', function () {
        throw ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);
    });

    $this->getJson('/validation-opt-out')
        ->assertStatus(422)
        ->assertJsonMissingPath('status')
        ->assertJsonStructure(['message', 'errors']);
});

test('authentication opt-out maps to the parent unauthenticated hook', function () {
    $this->useResponseConfig([
        'exception' => [
            AuthenticationException::class => false,
        ],
    ]);

    Route::get('/auth-opt-out', function () {
        throw new AuthenticationException('Login required.');
    });

    $this->getJson('/auth-opt-out')
        ->assertStatus(401)
        ->assertJsonMissingPath('status')
        ->assertJsonPath('message', 'Login required.');
});

test('non-json authentication requests still redirect', function () {
    Route::get('/login', fn () => 'login-page')->name('login');
    Route::get('/auth-web', function () {
        throw new AuthenticationException('Login required.');
    });

    $this->get('/auth-web')->assertRedirect('/login');
});
