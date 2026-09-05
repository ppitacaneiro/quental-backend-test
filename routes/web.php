<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs/api', function () {
    return view('docs.api');
});

Route::get('/docs/openapi.yaml', function (): Response {
    return response(file_get_contents(base_path('docs/openapi.yaml')), 200, [
        'Content-Type' => 'application/yaml',
    ]);
});
