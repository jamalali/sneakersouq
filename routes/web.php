<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchSneakersController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');

    Route::get('/search-sneakers', [SearchSneakersController::class, 'index'])->name('search-sneakers');
    Route::post('/search-sneakers', [SearchSneakersController::class, 'shopifySync'])->name('shopify-sync');
    // Route::get('/sneaker/{sneakerId}/{cacheKey?}/{shopifyProductId?}', [SneakerController::class, 'show'])->name('sneaker');
    // Route::post('/sneaker/shopify-up', [SneakerController::class, 'shopifyUp'])->name('shopify-up');

    Route::get('/agents', [AgentsController::class, 'index'])->name('agents.index');
    Route::get('/agents/{agentId}', [AgentsController::class, 'show'])->name('agents.show');
    Route::post('/agents/{agentId}/sync', [AgentsController::class, 'sync'])->name('agents.sync');
});

require __DIR__.'/auth.php';
