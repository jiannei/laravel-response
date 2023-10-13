<?php

use Jiannei\Response\Laravel\Support\Facades\Response;


test('add extra field', function () {
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toHaveKey('extra')
        ->and($response->getData(true)['extra'])->toHaveKey('time');

});
