<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsrfController;

Route::get('/ssrf', [SsrfController::class, 'index']);
Route::post('/ssrf/vulnerable', [SsrfController::class, 'vulnerable']);
Route::post('/ssrf/secure', [SsrfController::class, 'secure']);

// Ruta interna SOLO para demostrar SSRF
Route::get('/internal-secret', function () {
    return 'INTERNAL SECRET - solo accesible desde el servidor';
});
