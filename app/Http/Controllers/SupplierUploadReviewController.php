<?php

namespace App\Http\Controllers;

use App\Models\SupplierUpload;
use App\Models\Invoice; // Untuk dropdown faktur
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupplierUploadReviewController extends Controller
{
    public function index()
    {
        // 1. Ambil unggahan yang belum terkait, beserta relasi supplier
        $unlinkedUploads = SupplierUpload::with('supplier')
                                        ->where('is_linked', false)
                                        ->orderBy('created_at', 'desc')
                                        ->paginate(10);

        // 2. Ambil ID supplier dari unggahan yang ditampilkan di halaman ini
        $supplierIds = $unlinkedUploads->pluck('supplier_id')->unique()->filter();

        // 3. Ambil semua faktur yang relevan untuk supplier-supplier tersebut
        //    dan kelompokkan berdasarkan 'supplier_id' untuk kemudahan di view
        $invoicesBySupplier = [];
        if ($supplierIds->isNotEmpty()) {
            $invoicesBySupplier = Invoice::whereIn('supplier_id', $supplierIds)
                                        ->orderBy('invoice_number')
                                        ->get(['id', 'invoice_number', 'invoice_name', 'supplier_id'])
                                        ->groupBy('supplier_id');
        }

        // 4. Kirim data ke view
        return view('manager.supplier_uploads.index', compact('unlinkedUploads', 'invoicesBySupplier'));
    }

    // Mengaitkan gambar ke faktur
    public function linkToInvoice(Request $request, SupplierUpload $supplierUpload)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        // Pastikan upload belum dikaitkan
        if ($supplierUpload->is_linked) {
            return back()->with('error', 'Gambar sudah dikaitkan ke faktur lain.');
        }

        try {
            $supplierUpload->update([
                'is_linked' => true,
                'invoice_id' => $request->invoice_id,
            ]);

            // Opsional: Jika Anda ingin gambar juga muncul di InvoiceImage,
            // Anda bisa menyalinnya atau hanya menggunakan relasi SupplierUpload ke Invoice
            // Contoh menyalin:
            $invoice = Invoice::find($request->invoice_id);
            if ($invoice) {
                $invoice->invoiceImages()->create([
                    'filename' => $supplierUpload->filename,
                    'filepath' => $supplierUpload->filepath,
                    'title' => $supplierUpload->title,
                ]);
            }

            Log::info('Supplier upload linked to invoice. Upload ID: ' . $supplierUpload->id . ' -> Invoice ID: ' . $request->invoice_id);
            return back()->with('success', 'Gambar berhasil dikaitkan ke faktur.');

        } catch (\Exception $e) {
            Log::error('Error linking supplier upload: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengaitkan gambar ke faktur: ' . $e->getMessage());
        }
    }

    // Menghapus unggahan supplier (jika tidak relevan)
    public function destroy(SupplierUpload $supplierUpload)
    {
        try {
            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists($supplierUpload->filepath)) {
                Storage::disk('public')->delete($supplierUpload->filepath);
                Log::info('Deleted physical file: ' . $supplierUpload->filepath);
            }
            $supplierUpload->delete(); // Hapus entri dari database

            Log::info('Supplier upload deleted. Upload ID: ' . $supplierUpload->id);
            return back()->with('success', 'Unggahan supplier berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error deleting supplier upload: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus unggahan supplier: ' . $e->getMessage());
        }
    }
}