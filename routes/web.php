<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PublicImageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Penting untuk Auth::check()

// Rute root aplikasi
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Jika sudah login, arahkan ke dashboard
    }
    return redirect()->route('login'); // Jika belum login, arahkan ke halaman login
});

// Rute publik untuk melihat gambar faktur tanpa perlu login
Route::get('/invoice/image/{publicCode}/{filename}', [PublicImageController::class, 'show'])->name('public.invoice.image');

// Grup Rute yang memerlukan autentikasi
Route::middleware(['auth', 'verified'])->group(function () {
    // Halaman Utama (Dashboard) - Akses oleh Manajer & Pegawai
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Halaman Lihat Faktur & Detail Faktur - Akses oleh Manajer & Pegawai
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Halaman Pencarian Faktur - Akses oleh Manajer & Pegawai
    Route::get('/search-invoices', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search-invoices/results', [SearchController::class, 'results'])->name('search.results');


    // Grup Rute khusus untuk Manajer (memerlukan peran 'manager')
    Route::middleware(['role:manager'])->group(function () {
        // Operasi Faktur
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
        Route::post('/invoices/{invoice}/unmark-paid', [InvoiceController::class, 'unmarkPaid'])->name('invoices.unmarkPaid');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy'); // Soft delete

        // Tambah Faktur (Step 1 & 2)
        Route::get('/invoices/create/step1', [InvoiceController::class, 'createStep1'])->name('invoices.create.step1');
        Route::post('/invoices/create/step2', [InvoiceController::class, 'createStep2'])->name('invoices.create.step2');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store'); // Menyimpan faktur baru

        // Pengelolaan Supplier (dari popup)
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

        // Upload Gambar Faktur
        Route::post('/invoices/{invoice}/upload-image', [InvoiceController::class, 'uploadImage'])->name('invoices.uploadImage');
    });
});

// Memuat rute autentikasi Breeze (login, register, logout, dll.)
require __DIR__.'/auth.php';