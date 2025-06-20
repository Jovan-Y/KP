<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Menampilkan halaman daftar semua supplier.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Menyimpan supplier baru dari popup.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'required|email|max:255|unique:suppliers,email',
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan!',
            'supplier' => $supplier
        ]);
    }

    /**
     * Menghapus supplier dari database.
     */
    public function destroy(Supplier $supplier)
    {
        // Cek apakah supplier memiliki faktur terkait untuk mencegah error.
        if ($supplier->invoices()->exists()) {
            return back()->with('error', 'Gagal! Supplier tidak dapat dihapus karena sudah memiliki faktur terkait.');
        }

        $supplier->delete();

        return back()->with('success', 'Supplier berhasil dihapus.');
    }
}
