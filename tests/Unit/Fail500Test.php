<?php

use Jiannei\Response\Laravel\Support\Facades\Response;

test('fail with 500 status code', function () {
    $response = Response::fail();

    expect($response->status())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});