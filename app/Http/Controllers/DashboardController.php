<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SupplierUpload; // Import model SupplierUpload
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $unpaidInvoicesCount = Invoice::where('is_paid', false)->count();

        $dueDateSoonInvoices = Invoice::where('is_paid', false)
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date', 'asc')
            ->get();

        // Hitung unggahan supplier yang belum dikaitkan
        $newSupplierUploadsCount = SupplierUpload::where('is_linked', false)->count(); // <-- BARU

        return view('dashboard', compact('unpaidInvoicesCount', 'dueDateSoonInvoices', 'newSupplierUploadsCount')); // <-- Tambahkan
    }
}