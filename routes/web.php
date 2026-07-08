<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard.index')
    ->name('dashboard');

Route::get('/countries', [CountryController::class, 'index'])
    ->name('countries.index');

Route::get('/currency', [CurrencyController::class, 'index'])
    ->name('currency.index');

Route::get('/ports', [PortController::class, 'index'])
    ->name('ports.index');

Route::get('/news', [NewsController::class, 'index'])
    ->name('news.index');