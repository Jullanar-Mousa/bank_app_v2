<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\TransactionIngestionController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api.client')->prefix('v1')->group(function () {
    Route::post('/transactions/ingest', [TransactionIngestionController::class, 'store'])->name('api.transactions.ingest');
});