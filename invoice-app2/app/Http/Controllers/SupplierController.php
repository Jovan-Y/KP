<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{

    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email',
            'phone' => 'required|string|max:255|unique:suppliers,phone',
            'address' => 'nullable|string',

            'payment_details' => 'required|array',
            'payment_details.*.bank_name' => 'required|string|max:255',
            'payment_details.*.account_name' => 'required|string|max:255',
            'payment_details.*.account_number' => 'required|string|max:255',

        ], [
            'company_name.required' => 'Nama perusahaan harus diisi.',
            'company_name.unique'   => 'Nama perusahaan sudah terdaftar.',
            'name.required'         => 'Nama PIC harus diisi.',
            'email.required'        => 'Email PIC harus diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'phone.required'        => 'Nomor telepon harus diisi.',
            'phone.unique'          => 'Nomor telepon sudah terdaftar.',

            'payment_details.required' => 'Minimal satu detail pembayaran harus diisi.',
            'payment_details.*.bank_name.required' => 'Nama bank harus diisi.',
            'payment_details.*.account_name.required' => 'Nama pemilik rekening harus diisi.',
            'payment_details.*.account_number.required' => 'Nomor rekening harus diisi.',

        ]);

        try {
            Supplier::create($validatedData);
            if ($request->wantsJson()) {
                $latestSupplier = Supplier::latest('id')->first();
                return response()->json([
                    'success' => true, 
                    'message' => 'Supplier berhasil ditambahkan.',
                    'supplier' => $latestSupplier 
                ]);
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


    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
             Log::error('Gagal menghapus supplier: ' . $e->getMessage());
             return back()->with('error', 'Terjadi kesalahan saat menghapus supplier.');
        }
    }
}
