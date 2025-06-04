<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all(); // Untuk pilihan dropdown supplier
        return view('search.index', compact('suppliers'));
    }

    public function results(Request $request)
    {
        $query = Invoice::query()->with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('due_date_proximity')) {
            // Contoh: Mencari faktur yang akan jatuh tempo dalam 7 hari
            if ($request->input('due_date_proximity') == 'next_7_days') {
                $query->where('due_date', '>=', now())->where('due_date', '<=', now()->addDays(7));
            }
            // Tambahkan logika untuk rentang waktu lain jika diperlukan
        }

        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->input('invoice_number') . '%');
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('search.results', compact('invoices'));
    }
}