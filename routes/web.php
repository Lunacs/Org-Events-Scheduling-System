<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'directories.welcome');

Route::view('dashboard', 'directories.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'directories.profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('event-req', function () {
    return view('directories.event-req');
})->middleware(['auth']);

require __DIR__.'/auth.php';
