<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Menampilkan daftar semua supplier.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Menyimpan supplier baru ke database.
     */
    public function store(Request $request)
    {
        // Aturan validasi
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email',
            // PERUBAHAN DI SINI: Menambahkan aturan 'unique' untuk nomor telepon
            'phone' => 'nullable|string|max:255|unique:suppliers,phone',
            'address' => 'nullable|string',
            'payment_details' => 'nullable|array',
            'payment_details.*.bank_name' => 'required_with:payment_details|string|max:255',
            'payment_details.*.account_name' => 'required_with:payment_details|string|max:255',
            'payment_details.*.account_number' => 'required_with:payment_details|string|max:255',
        ]);

        try {
            Supplier::create($validatedData);

            // Jika permintaan datang dari AJAX (fetch)
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Supplier berhasil ditambahkan.']);
            }
            
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan supplier: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menyimpan supplier.'], 500);
            }
            
            return back()->with('error', 'Terjadi kesalahan saat menyimpan supplier.');
        }
    }

    /**
     * Menghapus supplier.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            // Logika untuk menghapus faktur terkait jika diperlukan
            // $supplier->invoices()->delete();
            
            $supplier->delete();
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
             Log::error('Gagal menghapus supplier: ' . $e->getMessage());
             return back()->with('error', 'Terjadi kesalahan saat menghapus supplier.');
        }
    }
}
