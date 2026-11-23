<?php
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('orders/{order}', [ApiController::class, 'order']);
    Route::post('orders/{order}/konfirmasi', [ApiController::class, 'konfirmasi']);
    Route::post('orders/{order}/proses', [ApiController::class, 'proses']);
    Route::get('inventory/low-stock', [ApiController::class, 'lowStock']);
    Route::get('finance/summary', [ApiController::class, 'financeSummary']);
    Route::get('ai/{jenis}', [ApiController::class, 'ai']);
});