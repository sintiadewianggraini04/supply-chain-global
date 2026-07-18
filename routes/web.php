<?php

use App\Http\Controllers\Admin\AdminArticleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPortController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CountryComparisonController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FavoriteMonitoringController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\WeatherController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Login User dan Admin
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get(
        '/login',
        [AuthController::class, 'showUserLogin']
    )->name('login');

    Route::post(
        '/login',
        [AuthController::class, 'userLogin']
    )->name('login.store');

    Route::get(
        '/admin/login',
        [AuthController::class, 'showAdminLogin']
    )->name('admin.login');

    Route::post(
        '/admin/login',
        [AuthController::class, 'adminLogin']
    )->name('admin.login.store');
});

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard User
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::view(
        '/',
        'dashboard.index'
    )->name('dashboard');

    Route::get(
        '/countries',
        [CountryController::class, 'index']
    )->name('countries.index');

    Route::get(
        '/weather',
        [WeatherController::class, 'index']
    )->name('weather.index');

    Route::get(
        '/currency',
        [CurrencyController::class, 'index']
    )->name('currency.index');

    Route::get(
        '/ports',
        [PortController::class, 'index']
    )->name('ports.index');

    Route::get(
        '/news',
        [NewsController::class, 'index']
    )->name('news.index');

    /*
     * Artikel yang diterbitkan administrator.
     */
    Route::get(
        '/articles',
        [ArticleController::class, 'index']
    )->name('articles.index');

    Route::get(
        '/articles/{slug}',
        [ArticleController::class, 'show']
    )
        ->where(
            'slug',
            '[A-Za-z0-9\-]+'
        )
        ->name('articles.show');

    Route::get(
        '/comparison',
        [CountryComparisonController::class, 'index']
    )->name('comparison.index');

    Route::get(
        '/favorites',
        [FavoriteMonitoringController::class, 'index']
    )->name('favorites.index');

    Route::post(
        '/favorites/{country}',
        [FavoriteMonitoringController::class, 'store']
    )->name('favorites.store');

    Route::delete(
        '/favorites/{country}',
        [FavoriteMonitoringController::class, 'destroy']
    )->name('favorites.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(AdminMiddleware::class)
    ->group(function () {
        Route::get(
            '/',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/users',
            [AdminUserController::class, 'index']
        )->name('users.index');

        Route::post(
            '/users',
            [AdminUserController::class, 'store']
        )->name('users.store');

        Route::patch(
            '/users/{user}',
            [AdminUserController::class, 'update']
        )->name('users.update');

        Route::delete(
            '/users/{user}',
            [AdminUserController::class, 'destroy']
        )->name('users.destroy');

        Route::get(
            '/ports',
            [AdminPortController::class, 'index']
        )->name('ports.index');

        Route::post(
            '/ports',
            [AdminPortController::class, 'store']
        )->name('ports.store');

        Route::patch(
            '/ports/{port}',
            [AdminPortController::class, 'update']
        )->name('ports.update');

        Route::delete(
            '/ports/{port}',
            [AdminPortController::class, 'destroy']
        )->name('ports.destroy');

        Route::get(
            '/articles',
            [AdminArticleController::class, 'index']
        )->name('articles.index');

        Route::post(
            '/articles',
            [AdminArticleController::class, 'store']
        )->name('articles.store');

        Route::patch(
            '/articles/{article}',
            [AdminArticleController::class, 'update']
        )->name('articles.update');

        Route::delete(
            '/articles/{article}',
            [AdminArticleController::class, 'destroy']
        )->name('articles.destroy');
    });