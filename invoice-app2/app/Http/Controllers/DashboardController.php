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
        $today = now()->startOfDay();

        $overdueInvoicesQuery = Invoice::where('is_paid', false)->whereDate('due_date', '<', $today);

        $upcomingInvoicesQuery = Invoice::where('is_paid', false)
            ->where('due_date', '>=', $today)
            ->where('due_date', '<=', $today->copy()->addDays(3)->endOfDay());

        $unpaidInvoicesQuery = Invoice::where('is_paid', false);

        $unpaidInvoicesCount = $unpaidInvoicesQuery->count();
        $overdueInvoicesCount = $overdueInvoicesQuery->count();
        $upcomingInvoicesCount = $upcomingInvoicesQuery->count();
            

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