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
        // mengambil tanggal hari ini
        $today = now()->startOfDay();

        // MEMBUAT PERSIAPAN QUERY DATABASE
        // 1. persiapan query untuk mencari faktur lewat jatuh tempo
        $overdueInvoicesQuery = Invoice::where('is_paid', false)->whereDate('due_date', '<', $today);

        // 2. persiapan query untuk mencari faktur yang 3 hari lagi jatuh tempo
        $upcomingInvoicesQuery = Invoice::where('is_paid', false)
            ->where('due_date', '>=', $today)
            ->where('due_date', '<=', $today->copy()->addDays(3)->endOfDay());

        // 3. query untuk mencari faktur yang belum lunas
        $unpaidInvoicesQuery = Invoice::where('is_paid', false);


        //MENJALANKAN QUERY DAN MENGHITUNG
        $unpaidInvoicesCount = $unpaidInvoicesQuery->count();   
        $overdueInvoicesCount = $overdueInvoicesQuery->count();  
        $upcomingInvoicesCount = $upcomingInvoicesQuery->count(); 

        $overdueInvoices = $overdueInvoicesQuery->with('supplier')->orderBy('due_date', 'asc')->get();

        $upcomingInvoices = $upcomingInvoicesQuery->with('supplier')->orderBy('due_date', 'asc')->get();


        // MENGIRIM SEMUA DATA KE VIEW
        return view('dashboard', compact(
            'unpaidInvoicesCount',
            'overdueInvoicesCount',
            'upcomingInvoicesCount',
            'overdueInvoices',
            'upcomingInvoices'
        ));
    }
}
