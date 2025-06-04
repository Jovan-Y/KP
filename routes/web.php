<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PublicImageController; // Untuk menampilkan gambar publik
use App\Http\Controllers\SupplierUploadController; // CONTROLLER BARU
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SupplierUploadReviewController;

use App\Mail\SupplierOtpMail; // Pastikan ini diimpor jika Anda pakai


// --- RUTE PUBLIK (TIDAK MEMERLUKAN LOGIN) ---

// Rute root aplikasi
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Rute untuk melihat gambar faktur secara publik (tetap ada)
Route::get('/invoice/image/{publicCode}/{filename}', [PublicImageController::class, 'show'])->name('public.invoice.image');

// ---- ALUR UNGGAH GAMBAR OLEH SUPPLIER (DENGAN OTP EMAIL) ----
// Langkah 1: Tampilkan form input email supplier
Route::get('/supplier-upload', [PublicImageController::class, 'showSupplierEmailForm'])->name('public.supplier.upload_form');
// Langkah 2: Proses email, kirim OTP
Route::post('/supplier-upload/send-otp', [PublicImageController::class, 'sendSupplierOtp'])->name('public.supplier.send_otp');
// Langkah 3: Tampilkan form verifikasi OTP
Route::get('/supplier-upload/verify-otp', [PublicImageController::class, 'showVerifyOtpForm'])->name('public.supplier.verify_otp_form');
// Langkah 4: Verifikasi OTP
Route::post('/supplier-upload/verify-otp', [PublicImageController::class, 'verifySupplierOtp'])->name('public.supplier.verify_otp');
// Langkah 5: Tampilkan form upload gambar independen
Route::get('/supplier-upload/upload-image', [PublicImageController::class, 'showIndependentUploadForm'])->name('public.supplier.upload_image_form');
// Langkah 6: Proses upload gambar independen
Route::post('/supplier-upload/upload-image', [PublicImageController::class, 'storeIndependentUpload'])->name('public.supplier.store_upload');
// Halaman sukses upload independen
Route::get('/supplier-upload/success', [PublicImageController::class, 'uploadIndependentSuccess'])->name('public.supplier.upload_success');

// --- RUTE TERLINDUNGI (MEMERLUKAN LOGIN) ---
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
        // Operasi Faktur (Manajer)
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.markPaid');
        Route::post('/invoices/{invoice}/unmark-paid', [InvoiceController::class, 'unmarkPaid'])->name('invoices.unmarkPaid');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        // Tambah Faktur (Step 1 & 2)
        Route::get('/invoices/create/step1', [InvoiceController::class, 'createStep1'])->name('invoices.create.step1');
        Route::post('/invoices/create/step2', [InvoiceController::class, 'createStep2'])->name('invoices.create.step2');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

        // Pengelolaan Supplier (dari popup)
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

        // ---- MANAJEMEN GAMBAR SUPPLIER (BARU UNTUK MANAJER) ----
        Route::get('/supplier-uploads-review', [SupplierUploadReviewController::class, 'index'])->name('manager.supplier_uploads.index');
    Route::post('/supplier-uploads-review/{supplierUpload}/link', [SupplierUploadReviewController::class, 'linkToInvoice'])->name('manager.supplier_uploads.link');
    Route::delete('/supplier-uploads-review/{supplierUpload}', [SupplierUploadReviewController::class, 'destroy'])->name('manager.supplier_uploads.destroy');
        // Rute ini digantikan oleh alur supplier.upload.store baru
        // Route::post('/invoices/{invoice}/upload-image', [InvoiceController::class, 'uploadImage'])->name('invoices.uploadImage');
    });
});

// Memuat rute autentikasi Breeze (login, register, logout, dll.)
require __DIR__.'/auth.php';