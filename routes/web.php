<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Arahkan ke dashboard sesuai peran
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'donor' => redirect()->route('donor.dashboard'),
            default => redirect()->route('receiver.dashboard'),
        };
    })->name('dashboard');

    Route::get('/donor/dashboard', fn () => view('dashboard.donor'))
        ->middleware(RoleMiddleware::class . ':donor')->name('donor.dashboard');

    Route::get('/receiver/dashboard', fn () => view('dashboard.receiver'))
        ->middleware(RoleMiddleware::class . ':receiver')->name('receiver.dashboard');

    Route::get('/admin/dashboard', fn () => view('dashboard.admin'))
        ->middleware(RoleMiddleware::class . ':admin')->name('admin.dashboard');

    // Tahap 4: barang donasi (khusus donatur)
    Route::middleware(RoleMiddleware::class . ':donor')->prefix('donor')->group(function () {
        Route::resource('items', ItemController::class)->except('show')->names('donor.items');
    });
});
