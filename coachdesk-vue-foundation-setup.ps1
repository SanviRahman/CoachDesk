$ErrorActionPreference = 'Stop'
$projectRoot = 'D:\coachdesk'
if ((Get-Location).Path -ne $projectRoot) { throw "Open PowerShell in $projectRoot before running this script." }

$backupRoot = Join-Path $projectRoot ('.setup-backups\vue-foundation-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
$filesToBackup = @(
    'vite.config.js',
    'resources\css\app.css',
    'routes\web.php',
    'bootstrap\app.php',
    'package.json',
    'Agent.md',
    'docs\project-overview.md',
    'docs\PRD.md',
    'docs\ERD.md',
    'docs\database-schema.md',
    'docs\folder-structure.md'
)

function Write-ProjectFile {
    param(
        [string]$RelativePath,
        [string]$Content
    )

    $fullPath = Join-Path $projectRoot $RelativePath
    [System.IO.File]::WriteAllText(
        $fullPath,
        $Content,
        [System.Text.UTF8Encoding]::new($false)
    )
}
foreach ($relativePath in $filesToBackup) {
    $source = Join-Path $projectRoot $relativePath
    if (Test-Path $source) {
        $destination = Join-Path $backupRoot $relativePath
        New-Item -ItemType Directory -Force -Path (Split-Path $destination) | Out-Null
        Copy-Item $source $destination
    }
}

New-Item -ItemType Directory -Force -Path 'resources\js\router', 'resources\js\views', 'resources\js\api', 'resources\css', 'resources\views', 'routes' | Out-Null

Write-ProjectFile -RelativePath 'vite.config.js' -Content @'
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
'@

Write-ProjectFile -RelativePath 'tsconfig.json' -Content @'
{
    "compilerOptions": {
        "target": "ES2022",
        "useDefineForClassFields": true,
        "module": "ESNext",
        "moduleResolution": "Bundler",
        "strict": true,
        "jsx": "preserve",
        "resolveJsonModule": true,
        "isolatedModules": true,
        "esModuleInterop": true,
        "skipLibCheck": true,
        "noEmit": true,
        "types": ["vite/client", "node"],
        "paths": {
            "@/*": ["resources/js/*"]
        }
    },
    "include": [
        "resources/js/**/*.ts",
        "resources/js/**/*.d.ts",
        "resources/js/**/*.vue"
    ],
    "exclude": ["node_modules"]
}
'@

Write-ProjectFile -RelativePath 'resources/js/env.d.ts' -Content @'
/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent<{}, {}, any>;
    export default component;
}
'@

Write-ProjectFile -RelativePath 'resources/js/app.ts' -Content @'
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

import 'bootstrap';
import 'admin-lte';

createApp(App)
    .use(createPinia())
    .use(router)
    .mount('#app');
'@

Write-ProjectFile -RelativePath 'resources/js/App.vue' -Content @'
<template>
    <RouterView />
</template>
'@

Write-ProjectFile -RelativePath 'resources/js/router/index.ts' -Content @'
import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '@/views/DashboardView.vue';
import NotFoundView from '@/views/NotFoundView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: { name: 'dashboard' } },
        { path: '/dashboard', name: 'dashboard', component: DashboardView },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView },
    ],
});

export default router;
'@

Write-ProjectFile -RelativePath 'resources/js/views/DashboardView.vue' -Content @'
<script setup lang="ts">
const summaryCards = [
    { label: 'Students', value: '0', icon: 'bi-people', color: 'text-primary' },
    { label: 'Teachers', value: '0', icon: 'bi-person-workspace', color: 'text-success' },
    { label: 'Courses', value: '0', icon: 'bi-journal-bookmark', color: 'text-warning' },
    { label: 'Batches', value: '0', icon: 'bi-collection', color: 'text-danger' },
];
</script>

<template>
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <span class="navbar-brand fw-semibold">CoachDesk</span>
                <span class="badge text-bg-success ms-auto">Vue SPA Ready</span>
            </div>
        </nav>

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="row g-3">
                        <div v-for="card in summaryCards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i :class="['bi', card.icon, card.color, 'fs-2']"></i>
                                    <div>
                                        <p class="text-body-secondary mb-1">{{ card.label }}</p>
                                        <h3 class="mb-0">{{ card.value }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Welcome to CoachDesk</h5>
                            <p class="card-text mb-0">
                                Laravel 13, Vue 3, Pinia, Vue Router and AdminLTE 4 are connected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
'@

Write-ProjectFile -RelativePath 'resources/js/views/NotFoundView.vue' -Content @'
<template>
    <div class="container py-5">
        <div class="card mx-auto text-center" style="max-width: 520px">
            <div class="card-body p-5">
                <h1 class="display-5 fw-semibold">404</h1>
                <p class="text-body-secondary">The page you requested was not found.</p>
                <RouterLink to="/dashboard" class="btn btn-primary">Back to Dashboard</RouterLink>
            </div>
        </div>
    </div>
</template>
'@

Write-ProjectFile -RelativePath 'resources/js/api/http.ts' -Content @'
import axios from 'axios';

const http = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

export default http;
'@

Write-ProjectFile -RelativePath 'resources/css/app.css' -Content @'
@import "bootstrap-icons/font/bootstrap-icons.css";
@import "overlayscrollbars/styles/overlayscrollbars.css";
@import "admin-lte/dist/css/adminlte.min.css";
@import "./adminlte-overrides.css";
'@

Write-ProjectFile -RelativePath 'resources/css/adminlte-overrides.css' -Content @'
body {
    min-width: 320px;
    min-height: 100vh;
}
'@

Write-ProjectFile -RelativePath 'resources/views/app.blade.php' -Content @'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CoachDesk</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
'@

Write-ProjectFile -RelativePath 'routes/web.php' -Content @'
<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
Route::view('/{any}', 'app')
    ->where('any', '^(?!api(?:/|$)|up$).*$');
'@

Write-ProjectFile -RelativePath 'routes/api.php' -Content @'
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'application' => config('app.name'),
        ]);
    });
});
'@

Write-ProjectFile -RelativePath 'bootstrap/app.php' -Content @'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
'@

$docsToUpdate = @(
    'Agent.md',
    'docs\project-overview.md',
    'docs\PRD.md',
    'docs\ERD.md',
    'docs\database-schema.md',
    'docs\folder-structure.md'
)

foreach ($relativePath in $docsToUpdate) {
    $documentPath = Join-Path $projectRoot $relativePath
    if (Test-Path $documentPath) {
        $content = [System.IO.File]::ReadAllText($documentPath)
        $content = $content.Replace('Laravel 12', 'Laravel 13')
        [System.IO.File]::WriteAllText(
            $documentPath,
            $content,
            [System.Text.UTF8Encoding]::new($false)
        )
    }
}

npm pkg set 'scripts.build=vue-tsc --noEmit && vite build'
if ($LASTEXITCODE -ne 0) { throw 'Failed to update the package.json build script.' }

Write-Host ''
Write-Host 'CoachDesk Vue foundation files created.' -ForegroundColor Green
Write-Host "Backup saved at: $backupRoot" -ForegroundColor Yellow
Write-Host 'Next: run npm run build and php artisan route:list.'
