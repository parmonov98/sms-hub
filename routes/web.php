<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// TEMP: Auth check endpoint for production debugging
Route::middleware('web')->get('/whoami', function () {
    return [
        'auth' => auth()->check(),
        'user' => optional(auth()->user())->only(['id', 'email', 'is_admin']),
    ];
});
