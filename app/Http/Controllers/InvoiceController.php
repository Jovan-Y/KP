<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Support\Facades\Response;
use App\Models\InvoiceItem;
use App\Models\InvoiceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Halaman 2: Lihat Faktur
    public function index(Request $request)
    {
        $invoices = Invoice::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Paginate untuk performa
        return view('invoices.index', compact('invoices'));
    }

    // Halaman 2.2: Detail Faktur
    public function show(Invoice $invoice)
    {
        $invoice->load('supplier', 'invoiceItems', 'invoiceImages');
        return view('invoices.show', compact('invoice'));
    }

    // Halaman 4: Tambah Faktur - Langkah 1 (Pilih Supplier)
    public function createStep1()
    {
        $suppliers = Supplier::all();
        return view('invoices.create-step1', compact('suppliers'));
    }

    // Halaman 5: Tambah Faktur - Langkah 2 (Isi Detail Faktur)
    public function createStep2(Request $request)
    {
        Log::info('Entering createStep2 method. Request method: ' . $request->method());
        Log::info('Request data received: ' . json_encode($request->all()));

        try {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            Log::info('Validation PASSED for supplier_id. Selected Supplier ID: ' . $request->input('supplier_id'));

            $supplier = Supplier::findOrFail($request->input('supplier_id'));
            Log::info('Supplier found: ' . $supplier->name . ' (ID: ' . $supplier->id . ')');

            // --- PENTING: Gunakan ini untuk memastikan View dirender langsung ---
            return Response::make(
                view('invoices.create-step2', compact('supplier'))->render(),
                200,
                [
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                    'Pragma' => 'no-cache',
                    'Content-Type' => 'text/html; charset=UTF-8' // Pastikan ini disetel dengan benar
                ]
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('VALIDATION FAILED in createStep2: ' . json_encode($e->errors()));
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('AN UNEXPECTED ERROR occurred in createStep2: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Simpan Faktur Baru
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_name' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'received_date' => 'required|date',
            'time_zone' => 'required|in:WIB,WIT,WITA',
            'payment_method' => 'required|in:cash,credit',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'has_ppn' => 'boolean',
            'ppn_type' => 'nullable|in:included,excluded',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotalItems = 0;
            foreach ($request->input('items') as $item) {
                $subtotalItems += ($item['quantity'] * $item['price']);
            }

            $discount = (float)($request->input('discount') ?? 0);
            $shippingCost = (float)($request->input('shipping_cost') ?? 0);
            $ppnAmount = 0;

            $totalBeforePpn = $subtotalItems - $discount + $shippingCost;

            if ($request->boolean('has_ppn')) {
                if ($request->input('ppn_type') === 'excluded') {
                    $ppnAmount = $totalBeforePpn * 0.11;
                }
                // If included, PPN calculation is a bit different,
                // but for simplicity, we'll assume it's added on top for total.
                // Or if it's already included, the total is already inclusive.
                // For this example, we calculate it if excluded.
            }

            $totalAmount = $totalBeforePpn + $ppnAmount;

            $invoice = Invoice::create([
                'supplier_id' => $request->input('supplier_id'),
                'invoice_name' => $request->input('invoice_name'),
                'invoice_number' => $request->input('invoice_number'),
                'invoice_date' => $request->input('invoice_date'),
                'received_date' => $request->input('received_date'),
                'time_zone' => $request->input('time_zone'),
                'payment_method' => $request->input('payment_method'),
                'due_date' => $request->input('due_date'),
                'has_ppn' => $request->boolean('has_ppn'),
                'ppn_type' => $request->boolean('has_ppn') ? $request->input('ppn_type') : null,
                'subtotal_items' => $subtotalItems,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'ppn_amount' => $ppnAmount,
                'total_amount' => $totalAmount,
                'is_paid' => false,
                'public_code' => Str::random(32), // Generate unique public code
            ]);

            foreach ($request->input('items') as $item) {
                $invoice->invoiceItems()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Faktur berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan faktur: ' . $e->getMessage());
        }
    }

    // Soft Delete Faktur
    public function destroy(Invoice $invoice)
    {
        $invoice->delete(); // Menggunakan soft delete
        return redirect()->route('invoices.index')->with('success', 'Faktur berhasil dipindahkan ke sampah.');
    }

    // Tandai Faktur Lunas
    public function markPaid(Invoice $invoice)
    {
        $invoice->update(['is_paid' => true]);
        return back()->with('success', 'Faktur berhasil ditandai lunas.');
    }

    // Batalkan Pelunasan Faktur
    public function unmarkPaid(Invoice $invoice)
    {
        $invoice->update(['is_paid' => false]);
        return back()->with('success', 'Pelunasan faktur berhasil dibatalkan.');
    }

    // Upload Gambar Faktur
    public function uploadImage(Request $request, Invoice $invoice)
    {
        $request->validate([
            'invoice_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('invoice_image')) {
            $image = $request->file('invoice_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('public/invoice_images/' . $invoice->id, $filename); // Simpan di storage/app/public/invoice_images/{invoice_id}

            $invoice->invoiceImages()->create([
                'filename' => $filename,
                'filepath' => Storage::url($path), // Path yang bisa diakses publik
            ]);
            return back()->with('success', 'Gambar faktur berhasil diunggah.');
        }

        return back()->with('error', 'Gagal mengunggah gambar faktur.');
    }
}