<?php

use App\Http\Controllers\AdminController;
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

    Route::get('/receiver/dashboard', fn () => view('dashboard.receiver'))
        ->middleware(RoleMiddleware::class . ':receiver')->name('receiver.dashboard');

    // Donatur
    Route::middleware(RoleMiddleware::class . ':donor')->prefix('donor')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard.donor'))->name('donor.dashboard');
        Route::resource('items', ItemController::class)->except('show')->names('donor.items');
    });

    // Admin
    Route::middleware(RoleMiddleware::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/items', [AdminController::class, 'items'])->name('items.index');
        Route::post('/items/{item}/approve', [AdminController::class, 'approveItem'])->name('items.approve');
        Route::post('/items/{item}/reject', [AdminController::class, 'rejectItem'])->name('items.reject');

        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::post('/users/{user}/block', [AdminController::class, 'blockUser'])->name('users.block');
        Route::post('/users/{user}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');

        Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
        Route::post('/reports/{report}/handle', [AdminController::class, 'handleReport'])->name('reports.handle');
        Route::post('/reports/{report}/takedown', [AdminController::class, 'takedownReport'])->name('reports.takedown');
    });
});
