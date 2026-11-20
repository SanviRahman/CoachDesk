<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
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

    Route::middleware(['auth:sanctum', 'active.user'])->group(function (): void {
        Route::get('/auth/me', [AuthenticatedSessionController::class, 'show']);
        Route::post('/auth/logout', [AuthenticatedSessionController::class, 'destroy']);

        Route::prefix('users')->group(function (): void {
            Route::get('/trash', [UserController::class, 'trash']);
            Route::post('/bulk-action', [UserController::class, 'bulkAction']);
            Route::patch('/{user}/restore', [UserController::class, 'restore'])->middleware('can:users.restore');
            Route::delete('/{user}/force', [UserController::class, 'forceDelete'])->middleware('can:users.force_delete');
            Route::get('/', [UserController::class, 'index'])->middleware('can:users.view');
            Route::post('/', [UserController::class, 'store'])->middleware('can:users.create');
            Route::patch('/{user}/status', [UserController::class, 'updateStatus'])->middleware('can:users.status.update');
            Route::get('/{user}', [UserController::class, 'show'])->middleware('can:users.view');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('can:users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete');
        });

        Route::prefix('roles')->group(function (): void {
            Route::get('/trash', [RoleController::class, 'trash']);
            Route::post('/bulk-action', [RoleController::class, 'bulkAction']);
            Route::patch('/{role}/restore', [RoleController::class, 'restore'])->middleware('can:roles.restore');
            Route::delete('/{role}/force', [RoleController::class, 'forceDelete'])->middleware('can:roles.force_delete');
            Route::get('/', [RoleController::class, 'index'])->middleware('can:roles.view');
            Route::post('/', [RoleController::class, 'store'])->middleware('can:roles.create');
            Route::get('/{role}', [RoleController::class, 'show'])->middleware('can:roles.view');
            Route::put('/{role}', [RoleController::class, 'update'])->middleware('can:roles.update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('can:roles.delete');
        });

        Route::prefix('permissions')->group(function (): void {
            Route::get('/trash', [PermissionController::class, 'trash']);
            Route::post('/bulk-action', [PermissionController::class, 'bulkAction']);
            Route::patch('/{permission}/restore', [PermissionController::class, 'restore'])->middleware('can:permissions.restore');
            Route::delete('/{permission}/force', [PermissionController::class, 'forceDelete'])->middleware('can:permissions.force_delete');
            Route::get('/', [PermissionController::class, 'index'])->middleware('can:permissions.view');
            Route::post('/', [PermissionController::class, 'store'])->middleware('can:permissions.create');
            Route::get('/{permission}', [PermissionController::class, 'show'])->middleware('can:permissions.view');
            Route::put('/{permission}', [PermissionController::class, 'update'])->middleware('can:permissions.update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('can:permissions.delete');
        });
    });
});
