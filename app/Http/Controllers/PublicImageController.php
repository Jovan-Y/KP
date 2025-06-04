<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceImage;
use App\Models\Supplier;
use App\Models\SupplierUpload; // Import model baru
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Pastikan Mail diimpor
use App\Mail\SupplierOtpMail; // Pastikan Mailable ini ada
use Carbon\Carbon; // Untuk OTP expiration

class PublicImageController extends Controller
{
    // --- METODE LAMA UNTUK GAMBAR FAKTUR (public_code) - TETAP ADA ---
    public function show($publicCode, $filename)
    {
        $invoice = Invoice::where('public_code', $publicCode)->firstOrFail();
        $imagePath = 'public/invoice_images/' . $invoice->id . '/' . $filename;

        if (Storage::disk('public')->exists('invoice_images/' . $invoice->id . '/' . $filename)) {
            // Gunakan Storage::path untuk mendapatkan path fisik
            return response()->file(Storage::disk('public')->path('invoice_images/' . $invoice->id . '/' . $filename));
        }
        abort(404, 'Image not found.');
    }
    // Metode ini tidak lagi digunakan untuk alur upload baru, hanya untuk melihat gambar yang sudah terhubung dengan faktur.
    // public function uploadSuccess($publicCode) { ... } // Ini juga bisa disesuaikan atau dipertahankan untuk link lama


    // --- METODE BARU UNTUK ALUR UPLOAD SUPPLIER INDEPENDEN (HANYA DENGAN EMAIL & OTP) ---

    // Langkah 1: Menampilkan form input email supplier
    public function showSupplierEmailForm()
    {
        return view('public_uploads.supplier_email_form');
    }

    // Langkah 2: Memproses email, kirim OTP
    public function sendSupplierOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:suppliers,email', // Email harus ada di database supplier
        ]);

        $supplier = Supplier::where('email', $request->email)->first();

        // Generate OTP
        $otp = Str::random(6, 'numeric'); // Contoh OTP 6 digit angka
        $expiresAt = Carbon::now()->addMinutes(5); // OTP berlaku 5 menit

        // Simpan OTP ke database supplier
        $supplier->update([
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Kirim OTP via email
        try {
            Mail::to($supplier->email)->send(new SupplierOtpMail($otp, $expiresAt));
            Log::info('OTP sent to ' . $supplier->email . ' for supplier upload.');

            // Simpan email supplier di session untuk verifikasi OTP
            $request->session()->put('temp_supplier_email_for_otp', $supplier->email);

            return redirect()->route('public.supplier.verify_otp_form')->with('success', 'Kode OTP telah dikirimkan ke email Anda.');

        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return back()->withInput()->with('error', 'Gagal mengirim kode OTP. Mohon coba lagi. Error: ' . $e->getMessage());
        }
    }

    // Langkah 3: Menampilkan form verifikasi OTP
    public function showVerifyOtpForm(Request $request)
    {
        if (!$request->session()->has('temp_supplier_email_for_otp')) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi tidak valid. Silakan mulai lagi.');
        }
        $supplierEmail = $request->session()->get('temp_supplier_email_for_otp');
        return view('public_uploads.verify_otp_form', compact('supplierEmail'));
    }

    // Langkah 4: Memverifikasi OTP
    public function verifySupplierOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $supplierEmail = $request->session()->get('temp_supplier_email_for_otp');

        if (!$supplierEmail) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi verifikasi OTP tidak valid. Silakan mulai lagi.');
        }

        $supplier = Supplier::where('email', $supplierEmail)->first();

        if (!$supplier || $supplier->otp_code !== $request->otp || Carbon::now()->greaterThan($supplier->otp_expires_at)) {
            Log::warning('OTP verification failed for email: ' . $supplierEmail . ' - Provided OTP: ' . $request->otp);
            return back()->withInput()->with('error', 'Kode OTP tidak valid atau sudah kedaluwarsa.');
        }

        // OTP berhasil diverifikasi, hapus OTP dari database
        $supplier->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Simpan ID supplier yang sudah diverifikasi di session untuk upload selanjutnya
        $request->session()->put('verified_supplier_id_for_independent_upload', $supplier->id);

        Log::info('OTP verified successfully for email: ' . $supplierEmail . ' for independent upload.');

        // Redirect ke halaman upload gambar independen
        return redirect()->route('public.supplier.upload_image_form')->with('success', 'Verifikasi berhasil! Anda sekarang dapat mengunggah gambar.');
    }

    // Langkah 5: Menampilkan form upload gambar independen
    public function showIndependentUploadForm(Request $request)
    {
        $verifiedSupplierId = $request->session()->get('verified_supplier_id_for_independent_upload');
        if (!$verifiedSupplierId) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Akses tidak sah. Silakan mulai verifikasi dari awal.');
        }
        $supplier = Supplier::find($verifiedSupplierId); // Ambil objek supplier
        return view('public_uploads.independent_upload_form', compact('supplier'));
    }

    // Langkah 6: Memproses upload gambar independen
    public function storeIndependentUpload(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'supplier_reference_code' => 'nullable|string|max:255', // Kode referensi dari supplier
            'image_file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000', // Max 5MB
        ]);

        $verifiedSupplierId = $request->session()->get('verified_supplier_id_for_independent_upload');
        if (!$verifiedSupplierId) {
            Log::error('Independent image upload attempt without supplier verification in session.');
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi upload tidak valid. Silakan mulai verifikasi dari awal.');
        }

        try {
            $supplier = Supplier::find($verifiedSupplierId);
            if (!$supplier) {
                return redirect()->route('public.supplier.upload_form')->with('error', 'Supplier tidak ditemukan.');
            }

            if ($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $filename = Str::slug($originalName) . '_' . time() . '.' . $extension;

                $destinationPath = 'public/supplier_uploads/' . $supplier->id; // Folder baru untuk upload independen
                if (!Storage::disk('public')->exists($destinationPath)) {
                    Storage::disk('public')->makeDirectory($destinationPath);
                    Log::info('Created directory for supplier uploads: ' . $destinationPath);
                }

                $path = $image->storeAs($destinationPath, $filename, 'public');

                if (!$path) {
                    throw new \Exception('Failed to store image physically.');
                }

                SupplierUpload::create([
                    'supplier_id' => $supplier->id,
                    'filename' => $filename,
                    'filepath' => $path, // Simpan path relatif
                    'title' => $request->title,
                    'supplier_reference_code' => $request->supplier_reference_code,
                    'is_linked' => false,
                    'invoice_id' => null,
                ]);

                // Hapus data verifikasi dari session setelah upload berhasil
                $request->session()->forget(['temp_supplier_email_for_otp', 'verified_supplier_id_for_independent_upload']);

                Log::info('Independent supplier image uploaded successfully for supplier ID: ' . $supplier->id . ' - Filename: ' . $filename);

                return redirect()->route('public.supplier.upload_success')->with('success', 'Gambar berhasil diunggah!');
            }
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR DURING INDEPENDENT UPLOAD: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return back()->withInput()->with('error', 'Gagal mengunggah gambar: ' . $e->getMessage() . ' (Periksa log server untuk detail)');
        }
    }

    // Halaman sukses upload independen
    public function uploadIndependentSuccess()
    {
        return view('public_uploads.independent_upload_success');
    }
}