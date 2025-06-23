<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Gunakan satu titik waktu yang konsisten untuk semua query
        $today = now()->startOfDay();

        // 1. Logika Terlewat Jatuh Tempo: due_date secara eksplisit HARUS KURANG DARI awal hari ini.
        $overdueInvoicesQuery = Invoice::where('is_paid', false)->whereDate('due_date', '<', $today);

        // 2. Logika Akan Jatuh Tempo: due_date HARUS LEBIH DARI ATAU SAMA DENGAN awal hari ini, dan kurang dari 3 hari ke depan.
        $upcomingInvoicesQuery = Invoice::where('is_paid', false)
            ->where('due_date', '>=', $today)
            ->where('due_date', '<=', $today->copy()->addDays(3)->endOfDay());

        $unpaidInvoicesQuery = Invoice::where('is_paid', false);

        // Data untuk KPI Cards
        $unpaidInvoicesCount = $unpaidInvoicesQuery->count();
        $overdueInvoicesCount = $overdueInvoicesQuery->count();
        $upcomingInvoicesCount = $upcomingInvoicesQuery->count();
            
        // Data untuk Daftar Peringatan
        $overdueInvoices = $overdueInvoicesQuery->with('supplier')->orderBy('due_date', 'asc')->get();
        $upcomingInvoices = $upcomingInvoicesQuery->with('supplier')->orderBy('due_date', 'asc')->get();

        return view('dashboard', compact(
            'unpaidInvoicesCount',
            'overdueInvoicesCount',
            'upcomingInvoicesCount',
            'overdueInvoices',
            'upcomingInvoices'
        ));
    }
}