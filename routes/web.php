<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiverController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

// Katalog barang (terbuka untuk tamu)
Route::get('/barang', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/barang/{item}', [CatalogController::class, 'show'])->name('catalog.show');

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

    // Semua peran
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Penerima
    Route::post('/barang/{item}/ajukan', [ReceiverController::class, 'store'])
        ->middleware(RoleMiddleware::class . ':receiver')->name('receiver.requests.store');

    Route::middleware(RoleMiddleware::class . ':receiver')->prefix('receiver')->group(function () {
        Route::get('/dashboard', [ReceiverController::class, 'dashboard'])->name('receiver.dashboard');
        Route::get('/requests', [ReceiverController::class, 'requests'])->name('receiver.requests.index');
        Route::post('/requests/{donationRequest}/cancel', [ReceiverController::class, 'cancel'])->name('receiver.requests.cancel');
    });

    // Donatur
    Route::middleware(RoleMiddleware::class . ':donor')->prefix('donor')->group(function () {
        Route::get('/dashboard', [DonorController::class, 'dashboard'])->name('donor.dashboard');
        Route::resource('items', ItemController::class)->except('show')->names('donor.items');

        Route::get('/requests', [DonorController::class, 'requests'])->name('donor.requests.index');
        Route::post('/requests/{donationRequest}/approve', [DonorController::class, 'approve'])->name('donor.requests.approve');
        Route::post('/requests/{donationRequest}/reject', [DonorController::class, 'reject'])->name('donor.requests.reject');

        Route::get('/history', [DonorController::class, 'history'])->name('donor.history');
    });

    // Admin
    Route::middleware(RoleMiddleware::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/items', [AdminController::class, 'items'])->name('items.index');
        Route::post('/items/{item}/approve', [AdminController::class, 'approveItem'])->name('items.approve');
        Route::post('/items/{item}/reject', [AdminController::class, 'rejectItem'])->name('items.reject');

        Route::get('/requests', [AdminController::class, 'requests'])->name('requests.index');

        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::post('/users/{user}/block', [AdminController::class, 'blockUser'])->name('users.block');
        Route::post('/users/{user}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');

        Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');
        Route::post('/reports/{report}/handle', [AdminController::class, 'handleReport'])->name('reports.handle');
        Route::post('/reports/{report}/takedown', [AdminController::class, 'takedownReport'])->name('reports.takedown');
    });
});
