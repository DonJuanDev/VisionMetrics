<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect raiz para frontend
Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// Links rastreáveis (redirect)
Route::get('/r/{token}', [TrackingController::class, 'redirect'])->name('tracking.redirect');

// Frontend SPA - todas as outras rotas são capturadas pelo Vue Router
Route::get('/{any}', function () {
    return response()->file(public_path('index.html'));
})->where('any', '^(?!api|r/|health).*$')->name('spa');
