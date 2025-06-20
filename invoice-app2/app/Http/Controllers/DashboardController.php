<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon; // Pastikan Carbon di-import

class DashboardController extends Controller
{
    /**
     * Menampilkan data untuk halaman utama.
     */
    public function index()
    {
        // 1. Ambil faktur yang SUDAH TERLEWAT jatuh tempo
        $overdueInvoices = Invoice::with('supplier')
            ->where('is_paid', false)
            ->whereDate('due_date', '<', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->get();

        // 2. Ambil faktur yang jatuh tempo HARI INI atau BESOK (Mendesak)
        $urgentInvoices = Invoice::with('supplier')
            ->where('is_paid', false)
            ->whereDate('due_date', '>=', Carbon::today())
            ->whereDate('due_date', '<=', Carbon::today()->addDay())
            ->orderBy('due_date', 'asc')
            ->get();
            
        // 3. LOGIKA BARU: Ambil faktur yang akan jatuh tempo dalam 3 hari (tidak termasuk yang mendesak)
        $upcomingInvoices = Invoice::with('supplier')
            ->where('is_paid', false)
            ->whereDate('due_date', '>', Carbon::today()->addDay()) // Mulai dari lusa
            ->whereDate('due_date', '<=', Carbon::today()->addDays(3)) // Sampai 3 hari dari sekarang
            ->orderBy('due_date', 'asc')
            ->get();

        // 4. Hitung total faktur yang belum lunas (untuk pemberitahuan umum)
        $unpaidInvoicesCount = Invoice::where('is_paid', false)->count();

        // 5. Kirim semua data yang diperlukan ke view
        return view('dashboard', compact(
            'unpaidInvoicesCount',
            'overdueInvoices',
            'urgentInvoices',
            'upcomingInvoices'
        ));
    }
}
