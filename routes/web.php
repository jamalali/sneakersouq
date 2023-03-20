<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SearchSneakersController;
// use App\Http\Controllers\SneakerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/search-sneakers', [SearchSneakersController::class, 'index'])->name('search-sneakers');
    Route::post('/search-sneakers', [SearchSneakersController::class, 'shopifySync'])->name('shopify-sync');
    // Route::get('/sneaker/{sneakerId}/{cacheKey?}/{shopifyProductId?}', [SneakerController::class, 'show'])->name('sneaker');
    // Route::post('/sneaker/shopify-up', [SneakerController::class, 'shopifyUp'])->name('shopify-up');
});
