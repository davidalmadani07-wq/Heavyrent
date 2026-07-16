<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExcavatorController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// ---- Auth ----
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'me']);

// ---- Excavators ----
Route::get('/excavators', [ExcavatorController::class, 'index']);
Route::post('/excavators', [ExcavatorController::class, 'store']);
Route::put('/excavators/{excavator}', [ExcavatorController::class, 'update']);
Route::delete('/excavators/{excavator}', [ExcavatorController::class, 'destroy']);

// ---- Operators ----
Route::get('/operators', [OperatorController::class, 'index']);
Route::post('/operators', [OperatorController::class, 'store']);
Route::put('/operators/{operator}', [OperatorController::class, 'update']);
Route::delete('/operators/{operator}', [OperatorController::class, 'destroy']);

// ---- Bookings ----
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

Route::get('/artisan-reset-uas', function () {
    try {
        // Memaksa migrasi fresh dan seeding langsung di dalam server utama
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true,
        ]);
        return 'Database HeavyRent BERHASIL di-reset dan di-seed! Silakan kembali ke halaman utama.';
    } catch (\Exception $e) {
        return 'Waduh, gagal: ' . $e->getMessage();
    }
});
