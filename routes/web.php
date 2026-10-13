<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');

Route::get('/admin', function () {
    $account = auth('web')->user();

    abort_unless($account && $account->role === 'admin', 403);

    return view('admin.index');
})->middleware('auth:web')->name('admin.dashboard');

Route::get('/admin/employee-edit', function () {
    $account = auth('web')->user();

    abort_unless($account && $account->role === 'admin', 403);

    return view('admin.employee-edit');
})->middleware('auth:web')->name('admin.employee-edit');

Route::get('/manager', function () {
    $account = auth('web')->user();

    abort_unless($account && $account->role === 'manager', 403);

    return view('manager.index');
})->middleware('auth:web')->name('manager.dashboard');

Route::get('/employee', function () {
    $account = auth('web')->user();

    abort_unless($account && $account->role === 'employee', 403);

    return view('employee.index');
})->middleware('auth:web')->name('employee.dashboard');

Route::get('/settings', function () {
    $account = auth('web')->user();

    abort_unless(
        $account && in_array($account->role, ['manager', 'employee'], true),
        403
    );

    return view('settings.index');
})->middleware('auth:web')->name('settings.index');

Route::get('/manager/profile', function () {
    $account = auth('web')->user();

    abort_unless(
        $account && $account->role === 'manager',
        403
    );

    return view('manager.profile');
})->middleware('auth:web')->name('manager.profile');

Route::get('/employee/profile', function () {
    $account = auth('web')->user();

    abort_unless(
        $account && $account->role === 'employee',
        403
    );

    return view('employee.profile');
})->middleware('auth:web')->name('employee.profile');
