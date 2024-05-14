<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

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

Route::get('/', [ProjectController::class, 'index']);
Route::get('/about', [ProjectController::class, 'about']);
Route::get('/rules', [ProjectController::class, 'rules']);
Route::get('/help', [ProjectController::class, 'help']);

/* Only for Guests: OAuth */
Route::group(['middleware' => 'guest', 'prefix' => '/login'], function () {
    Route::get('/steam', LoginController::class)->name('login');
});

/* Auth */
Route::middleware('auth')->group(function () {
    Route::get('/market', [ProjectController::class, 'market']);
    Route::get('/p2p/{id}', [ProjectController::class, 'p2p']);
    /* API */
    Route::group(['prefix' => '/api'], function () {
        /* Market */
        Route::group(['prefix' => '/market'], function () {
            Route::post('/create', [ProjectController::class, 'marketCreate']);
            Route::post('/cancel', [ProjectController::class, 'marketCancel']);
        });
        /* P2P */
        Route::group(['prefix' => '/p2p'], function () {
            Route::post('/create', [ProjectController::class, 'p2pCreate']);
            Route::post('/send-trade', [ProjectController::class, 'p2pSendTrade']);
            Route::post('/accept-trade', [ProjectController::class, 'p2pAcceptTrade']);
            Route::post('/cancel', [ProjectController::class, 'p2pCancel']);
        });
        /* User */
        Route::group(['prefix' => '/user'], function () {
            Route::post('/set-trade', [ProjectController::class, 'setTrade']);
            /* Payment */
            Route::group(['prefix' => '/payment'], function () {
                Route::post('/create', [ProjectController::class, 'paymentCreate']);
                Route::post('/check', [ProjectController::class, 'paymentCheck']);
            });
        });
    });
    Route::get('/logout', [LoginController::class, 'logout']);
});
