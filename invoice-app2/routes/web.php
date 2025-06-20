<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Rute root aplikasi
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// --- RUTE TERLINDUNGI (MEMERLUKAN LOGIN) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/search-invoices', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search-invoices/results', [SearchController::class, 'results'])->name('search.results');
            // Operasi Faktur
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
        Route::post('/invoices/{invoice}/unmark-paid', [InvoiceController::class, 'unmarkPaid'])->name('invoices.unmarkPaid');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        
        // Rute untuk edit dan update TELAH DIHAPUS
        // Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        // Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');

        // Alur Tambah Faktur Baru
        Route::get('/invoices/create/step1', [InvoiceController::class, 'createStep1'])->name('invoices.create.step1');
        Route::post('/invoices/create/step1', [InvoiceController::class, 'postStep1'])->name('invoices.create.post_step1');
        Route::get('/invoices/create/step2', [InvoiceController::class, 'showStep2'])->name('invoices.create.show_step2');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

        // Operasi Gambar pada Faktur
        Route::post('/invoices/{invoice}/upload-payment-proof', [InvoiceController::class, 'uploadPaymentProof'])->name('invoices.uploadPaymentProof');
        Route::delete('/invoices/image/{image}', [InvoiceController::class, 'destroyImage'])->name('invoices.images.destroy');
        
    // === GRUP RUTE KHUSUS UNTUK MANAJER ===
    Route::middleware(['role:manager'])->group(function () {

        // Pengelolaan Supplier
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Pengelolaan Akun Pegawai
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.update_status');
        Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('users.update_password');

        
    });
});

require __DIR__.'/auth.php';

