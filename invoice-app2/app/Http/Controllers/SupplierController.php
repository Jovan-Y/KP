<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Menggunakan latest() untuk mengurutkan berdasarkan data terbaru
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // PERUBAHAN: Menyesuaikan nama field dengan database ('name' dan 'phone')
        $rules = [
            'company_name' => 'required|string|max:255',
            'name' => [ // Menggunakan 'name'
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers')->where(function ($query) use ($request) {
                    return $query->where('email', $request->email);
                }),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers')->where(function ($query) use ($request) {
                    return $query->where('name', $request->name); // Menggunakan 'name'
                }),
            ],
            'phone' => 'nullable|string|max:255', // Menggunakan 'phone'
            'address' => 'nullable|string',
            'payment_details' => 'present|array',
            'payment_details.*.bank_name' => 'required|string|max:255',
            'payment_details.*.account_number' => 'required|string|max:255',
            'payment_details.*.account_name' => 'required|string|max:255',
        ];

        $messages = [
            'name.unique' => 'Kombinasi Nama Kontak dan Email ini sudah terdaftar.', // Menyesuaikan pesan
            'email.unique' => 'Kombinasi Email dan Nama Kontak ini sudah terdaftar.',
            'payment_details.*.bank_name.required' => 'Nama bank wajib diisi.',
            'payment_details.*.account_number.required' => 'Nomor rekening wajib diisi.',
            'payment_details.*.account_name.required' => 'Nama pemilik rekening wajib diisi.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $supplier = Supplier::create($validatedData);

        return response()->json(['success' => 'Supplier berhasil ditambahkan!', 'supplier' => $supplier]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal menghapus supplier.');
        }
    }
}