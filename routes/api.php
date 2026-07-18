<?php

use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\CountryComparisonApiController;
use App\Http\Controllers\Api\CurrencyApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\PortApiController;
use App\Http\Controllers\Api\RiskApiController;
use App\Http\Controllers\Api\WeatherApiController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/dashboard',
    [DashboardApiController::class, 'index']
)->name('api.dashboard.index');

Route::get(
    '/news',
    [NewsApiController::class, 'index']
)->name('api.news.index');

Route::get(
    '/countries',
    [CountryApiController::class, 'index']
)->name('api.countries.index');

Route::get(
    '/weather',
    [WeatherApiController::class, 'index']
)->name('api.weather.index');

Route::get(
    '/currency',
    [CurrencyApiController::class, 'index']
)->name('api.currency.index');

Route::get(
    '/ports',
    [PortApiController::class, 'index']
)->name('api.ports.index');

Route::get(
    '/comparison',
    [CountryComparisonApiController::class, 'index']
)->name('api.comparison.index');

Route::get(
    '/risk',
    [RiskApiController::class, 'index']
)->name('api.risk.index');