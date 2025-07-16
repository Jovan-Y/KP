<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{

    public function index()
    {
        //ambil semua data supplier untuk ditampilkan di dropdown form pencarian
        $suppliers = Supplier::orderBy('company_name')->get();
        return view('search.index', compact('suppliers'));
    }


    public function results(Request $request)
    {
        // Validasi input tanggal
        $request->validate([
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
        ], [
            'due_date_to.after_or_equal' => 'Tanggal "Sampai" harus setelah atau sama dengan tanggal "Dari".'
        ]);

        // 1. MEMULAI PEMBUATAN QUERY
        $query = Invoice::query()->with('supplier');

        // 2. MENAMBAHKAN FILTER
        // metode when hanya akan menjalankan query di dalamnya
        //jika ada input 'invoice_number', tambahkan filter pencarian berdasarkan nomor faktur.
        $query->when($request->invoice_number, function ($query, $invoiceNumber) {
            // mengandung teks
            return $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
        });

        //jika ada input supplier_id
        $query->when($request->supplier_id, function ($query, $supplierId) {
            return $query->where('supplier_id', $supplierId);
        });

        //jika ada input jatuh tempo pada atau setelah tanggal tersebut
        $query->when($request->due_date_from, function ($query, $dateFrom) {
            return $query->whereDate('due_date', '>=', $dateFrom);
        });

        //jika ada input jatuh tempo pada atau sebelum tanggal tersebut.
        $query->when($request->due_date_to, function ($query, $dateTo) {
            return $query->whereDate('due_date', '<=', $dateTo);
        });

        //jika ada input berdasarkan status pembayaran.
        $query->when($request->status, function ($query, $status) {
            if ($status === 'paid') return $query->where('is_paid', true); 
            if ($status === 'unpaid') return $query->where('is_paid', false); 
        });

        // 3. MENJALANKAN QUERY DAN MENGAMBIL HASIL
        //setelah semua filter ditambahkan, jalankan query-nya dengan urutan tanggal
        //paginate(15) untuk menampilkan 15 hasil per halaman.
        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(15)->withQueryString();
        
        // 4. MEMPERSIAPKAN DATA UNTUK DITAMPILKAN DI VIEW
        $suppliers = Supplier::all(); 
        $searchCriteria = $request->only(['invoice_number', 'supplier_id', 'due_date_from', 'due_date_to', 'status']);

        // 5. TAMPILKAN HALAMAN HASIL
        return view('search.results', compact('invoices', 'searchCriteria', 'suppliers'));
    }
}
