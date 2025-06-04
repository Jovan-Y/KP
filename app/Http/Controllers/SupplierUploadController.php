<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierUpload; // Model baru Anda
use App\Models\Invoice; // Untuk pengaitan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\SupplierOtpMail; // Mailable OTP Anda

class SupplierUploadController extends Controller
{
    // --- Bagian untuk Supplier (Public Access) ---

    // Menampilkan form input email supplier (Langkah 1)
    public function showEmailInputForm()
    {
        return view('supplier_uploads.step1_email_input');
    }

    // Mengirim OTP ke email supplier (Langkah 2)
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:suppliers,email',
        ]);

        $supplier = Supplier::where('email', $request->email)->first();

        // Generate OTP
        $otp = rand(100000, 999999); // 6 digit angka
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

            // Simpan email supplier di session sementara
            $request->session()->put('temp_supplier_email_for_otp', $request->email);

            return redirect()->route('supplier.upload.verify_otp_form')->with('success', 'Kode OTP telah dikirimkan ke email Anda.');

        } catch (\Exception $e) {
            Log::error('Failed to send OTP email for supplier upload: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengirim kode OTP. Mohon coba lagi. Error: ' . $e->getMessage());
        }
    }

    // Menampilkan form verifikasi OTP (Langkah 3)
    public function verifyOtpForm(Request $request)
    {
        if (!$request->session()->has('temp_supplier_email_for_otp')) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi verifikasi tidak valid. Silakan mulai lagi.');
        }
        $supplierEmail = $request->session()->get('temp_supplier_email_for_otp');
        return view('supplier_uploads.step2_verify_otp', compact('supplierEmail'));
    }

    // Memverifikasi OTP (Langkah 4)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $supplierEmail = $request->session()->get('temp_supplier_email_for_otp');

        if (!$supplierEmail) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi verifikasi OTP tidak valid. Silakan mulai lagi.');
        }

        $supplier = Supplier::where('email', $supplierEmail)->first();

        if (!$supplier || $supplier->otp_code != $request->otp || Carbon::now()->greaterThan($supplier->otp_expires_at)) {
            Log::warning('OTP verification failed for supplier upload: ' . $supplierEmail . ' - Provided OTP: ' . $request->otp);
            return back()->withInput()->with('error', 'Kode OTP tidak valid atau sudah kedaluwarsa.');
        }

        // OTP berhasil diverifikasi, hapus OTP dari database supplier
        $supplier->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Simpan ID supplier yang diverifikasi di session untuk upload gambar
        $request->session()->put('verified_supplier_id_for_upload', $supplier->id);
        Log::info('OTP verified successfully for supplier upload: ' . $supplierEmail);

        // Redirect ke halaman upload gambar (Langkah 5)
        return redirect()->route('supplier.upload.upload_form');
    }

    // Menampilkan form upload gambar setelah OTP diverifikasi (Langkah 5)
    public function showUploadForm(Request $request)
    {
        $verifiedSupplierId = $request->session()->get('verified_supplier_id_for_upload');
        if (!$verifiedSupplierId) {
            return redirect()->route('public.supplier.upload_form')->with('error', 'Akses tidak sah. Silakan mulai verifikasi dari awal.');
        }
        return view('supplier_uploads.step3_upload_image');
    }

    // Memproses upload gambar oleh supplier (Langkah 6)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'upload_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
        ]);

        $verifiedSupplierId = $request->session()->get('verified_supplier_id_for_upload');
        if (!$verifiedSupplierId) {
            Log::error('Supplier upload attempt without verification in session.');
            return redirect()->route('public.supplier.upload_form')->with('error', 'Sesi upload tidak valid. Silakan mulai verifikasi dari awal.');
        }

        try {
            $image = $request->file('upload_image');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $uploadCode = Str::random(10); // Kode unik untuk gambar ini
            $filename = Str::slug($originalName) . '_' . $uploadCode . '.' . $extension;

            $destinationPath = 'public/supplier_uploads'; // Simpan di folder khusus supplier_uploads

            if (!Storage::disk('public')->exists($destinationPath)) {
                Storage::disk('public')->makeDirectory($destinationPath);
            }

            $path = $image->storeAs($destinationPath, $filename, 'public');

            if (!$path) {
                throw new \Exception('Failed to store image physically.');
            }

            SupplierUpload::create([
                'upload_code' => $uploadCode,
                'title' => $request->title,
                'filename' => $filename,
                'filepath' => $path, // Simpan path relatif
                'is_linked' => false,
                'linked_invoice_id' => null,
                'supplier_id' => $verifiedSupplierId, // Kaitkan dengan supplier yang mengunggah
            ]);

            // Hapus data verifikasi supplier dari session setelah upload berhasil
            $request->session()->forget(['temp_supplier_email_for_otp', 'verified_supplier_id_for_upload']);

            Log::info('New supplier image uploaded successfully. Filename: ' . $filename . ' - Upload Code: ' . $uploadCode);

            return redirect()->route('supplier.upload.success', ['uploadCode' => $uploadCode])
                            ->with('success', 'Gambar berhasil diunggah! Kode unik Anda adalah: ' . $uploadCode);

        } catch (\Exception $e) {
            Log::error('Error during supplier image upload: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return back()->withInput()->with('error', 'Gagal mengunggah gambar: ' . $e->getMessage());
        }
    }

    // Halaman sukses setelah upload supplier
    public function uploadSuccess($uploadCode)
    {
        $supplierUpload = SupplierUpload::where('upload_code', $uploadCode)->firstOrFail();
        return view('supplier_uploads.success', compact('supplierUpload'));
    }


    // --- Bagian untuk Manajer (Terlindungi) ---

    // Halaman Index untuk melihat semua gambar supplier yang belum terkait
    public function index()
    {
        $unlinkedUploadsCount = SupplierUpload::where('is_linked', false)->count();
        $supplierUploads = SupplierUpload::with('supplier', 'linkedInvoice')
                                        ->orderBy('created_at', 'desc')
                                        ->paginate(15);
        $invoices = Invoice::orderBy('invoice_number', 'asc')->get(['id', 'invoice_number', 'invoice_name']); // Untuk dropdown pengaitan

        return view('supplier_uploads.index', compact('supplierUploads', 'invoices', 'unlinkedUploadsCount'));
    }

    // Mengaitkan gambar supplier ke faktur tertentu
    public function linkToInvoice(Request $request, SupplierUpload $supplierUpload)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        try {
            // Buat entri baru di InvoiceImage (yang terkait dengan Invoice)
            $invoice = Invoice::findOrFail($request->invoice_id);

            $invoice->invoiceImages()->create([
                'filename' => $supplierUpload->filename,
                'filepath' => $supplierUpload->filepath, // Ambil path dari supplier_upload
                'title' => $supplierUpload->title,
            ]);

            // Update status SupplierUpload
            $supplierUpload->update([
                'is_linked' => true,
                'linked_invoice_id' => $invoice->id,
            ]);

            Log::info('Supplier image linked to invoice. Upload ID: ' . $supplierUpload->id . ' -> Invoice ID: ' . $invoice->id);

            return back()->with('success', 'Gambar berhasil dikaitkan ke faktur.');

        } catch (\Exception $e) {
            Log::error('Error linking supplier image to invoice: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengaitkan gambar: ' . $e->getMessage());
        }
    }

    // Menghapus gambar supplier (soft delete tidak digunakan di sini, langsung hapus)
    public function destroy(SupplierUpload $supplierUpload)
    {
        try {
            // Hapus file fisik dari storage
            Storage::disk('public')->delete($supplierUpload->filepath);

            // Hapus entri dari database
            $supplierUpload->delete();

            Log::info('Supplier upload image deleted. Upload ID: ' . $supplierUpload->id);

            return back()->with('success', 'Gambar supplier berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier upload: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus gambar supplier: ' . $e->getMessage());
        }
    }
}