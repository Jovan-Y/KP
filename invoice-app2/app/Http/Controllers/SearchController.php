<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Menampilkan halaman form pencarian.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('company_name')->get();
        return view('search.index', compact('suppliers'));
    }

    /**
     * Memproses pencarian dan menampilkan hasilnya.
     */
    public function results(Request $request)
    {
        $request->validate([
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
        ], [
            'due_date_to.after_or_equal' => 'Tanggal "Sampai" harus setelah atau sama dengan tanggal "Dari".'
        ]);

        $query = Invoice::query()->with('supplier');

        $query->when($request->invoice_number, function ($query, $invoiceNumber) {
            return $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
        });

        // Logika diubah untuk mencari berdasarkan ID supplier dari dropdown
        $query->when($request->supplier_id, function ($query, $supplierId) {
            return $query->where('supplier_id', $supplierId);
        });

        $query->when($request->due_date_from, function ($query, $dateFrom) {
            return $query->whereDate('due_date', '>=', $dateFrom);
        });

        $query->when($request->due_date_to, function ($query, $dateTo) {
            return $query->whereDate('due_date', '<=', $dateTo);
        });

        $query->when($request->status, function ($query, $status) {
            if ($status === 'paid') return $query->where('is_paid', true);
            if ($status === 'unpaid') return $query->where('is_paid', false);
        });

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(15)->withQueryString();
        
        // Kirim semua supplier agar bisa menampilkan nama di halaman hasil
        $suppliers = Supplier::all(); 
        $searchCriteria = $request->only(['invoice_number', 'supplier_id', 'due_date_from', 'due_date_to', 'status']);

        return view('search.results', compact('invoices', 'searchCriteria', 'suppliers'));
    }
}
