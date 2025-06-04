<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Menyimpan Supplier Baru dari Popup
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan!',
            'supplier' => $supplier
        ]);
    }
}