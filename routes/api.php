<?php

use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\CurrencyApiController;
use App\Http\Controllers\Api\PortApiController;
use Illuminate\Support\Facades\Route;

Route::get('/news', [NewsApiController::class, 'index'])
    ->name('api.news.index');

Route::get('/countries', [CountryApiController::class, 'index'])
    ->name('api.countries.index');

Route::get('/currency', [CurrencyApiController::class, 'index'])
    ->name('api.currency.index');

Route::get('/ports', [PortApiController::class, 'index'])
    ->name('api.ports.index');