<?php

use Illuminate\Support\Facades\Route;

Route::view('/login', 'auth.login')->name('login');

Route::get('/', function () {
    return view('welcome');
});
Route::get('/admin', function () {
    $account = auth('web')->user();

    abort_unless($account && $account->role === 'admin', 403);

    return view('admin.index');
})->middleware('auth:web')->name('admin.dashboard');