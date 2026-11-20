<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'application' => config('app.name'),
        ]);
    });

    Route::post('/auth/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthenticatedSessionController::class, 'show']);
        Route::post('/auth/logout', [AuthenticatedSessionController::class, 'destroy']);
    });
});
