<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\InvoiceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Menampilkan daftar semua faktur.
     */
    public function index()
    {
        $invoices = Invoice::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Menampilkan detail satu faktur.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('supplier', 'invoiceItems', 'paymentProofImages', 'referenceImages');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Menampilkan halaman untuk mengedit faktur.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->is_paid) {
            return redirect()->route('invoices.show', $invoice->id)->with('error', 'Faktur yang sudah lunas tidak dapat diedit.');
        }
        
        $invoice->load('invoiceItems', 'referenceImages');
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Memproses pembaruan data faktur.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->is_paid) {
            return redirect()->route('invoices.show', $invoice->id)->with('error', 'Faktur yang sudah lunas tidak dapat diubah.');
        }

        $validatedData = $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number,' . $invoice->id,
            'po_number' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'received_date' => 'required|date',
            'due_date' => 'nullable|date|required_if:payment_type,kredit|after_or_equal:invoice_date',
            'payment_type' => 'required|in:debit,kredit',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
            'other_taxes' => 'nullable|array',
            'other_taxes.*.name' => 'nullable|string|max:255',
            'other_taxes.*.type' => 'nullable|in:fixed,percentage',
            'other_taxes.*.value' => 'nullable|numeric|min:0',
            'reference_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
            'images_to_delete' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Hapus gambar lama yang ditandai
            if ($request->filled('images_to_delete')) {
                $imageIds = explode(',', $request->input('images_to_delete'));
                $imagesToDelete = InvoiceImage::whereIn('id', $imageIds)->get();
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete(str_replace('/storage', '', $image->filepath));
                    $image->delete();
                }
            }

            // 2. Unggah gambar baru
            if ($request->hasFile('reference_images')) {
                foreach ($request->file('reference_images') as $imageFile) {
                    if ($imageFile && $imageFile->isValid()) {
                        $path = $imageFile->store("invoice_images/{$invoice->id}/references", 'public');
                        $invoice->invoiceImages()->create([
                            'filename' => $imageFile->hashName(),
                            'filepath' => Storage::url($path),
                            'title'    => $imageFile->getClientOriginalName(),
                            'type'     => 'reference'
                        ]);
                    }
                }
            }
            
            // 3. Hapus item lama dan buat ulang
            $invoice->invoiceItems()->delete();
            $subtotalItems = 0;
            foreach ($validatedData['items'] as $itemData) {
                $subtotal = (float)$itemData['quantity'] * (float)$itemData['price'];
                $invoice->invoiceItems()->create([
                    'item_name' => $itemData['item_name'],
                    'quantity'  => $itemData['quantity'],
                    'unit'      => $itemData['unit'],
                    'price'     => $itemData['price'],
                    'subtotal'  => $subtotal,
                ]);
                $subtotalItems += $subtotal;
            }

            // 4. Hitung ulang semua total
            $discountValue = (float)($validatedData['discount_value'] ?? 0);
            $discountAmount = ($validatedData['discount_type'] === 'percentage') ? ($subtotalItems * $discountValue) / 100 : $discountValue;
            $taxBase = $subtotalItems - $discountAmount;
            $ppnPercentage = (float)($validatedData['ppn_percentage'] ?? 0);
            $ppnAmount = ($taxBase * $ppnPercentage) / 100;
            $totalOtherTaxesAmount = 0;
            $otherTaxesList = [];
            if (!empty($validatedData['other_taxes'])) {
                foreach ($validatedData['other_taxes'] as $tax) {
                    if (!empty($tax['name']) && isset($tax['value'])) {
                        $taxValue = (float)$tax['value'];
                        $taxAmount = ($tax['type'] === 'percentage') ? ($taxBase * $taxValue) / 100 : $taxValue;
                        $otherTaxesList[] = ['name' => $tax['name'], 'type' => $tax['type'], 'value' => $taxValue, 'amount' => $taxAmount];
                        $totalOtherTaxesAmount += $taxAmount;
                    }
                }
            }
            $totalAmount = $taxBase + $ppnAmount + $totalOtherTaxesAmount;

            // 5. Update data utama faktur
            $invoice->update([
                'invoice_number' => $validatedData['invoice_number'],
                'po_number' => $validatedData['po_number'],
                'invoice_date' => $validatedData['invoice_date'],
                'received_date' => $validatedData['received_date'],
                'due_date' => $validatedData['payment_type'] === 'kredit' ? $validatedData['due_date'] : null,
                'payment_type' => $validatedData['payment_type'],
                'subtotal_items' => $subtotalItems,
                'discount_type' => $validatedData['discount_type'],
                'discount_value' => $discountValue,
                'ppn_percentage' => $ppnPercentage,
                'ppn_amount' => $ppnAmount,
                'other_taxes' => $otherTaxesList,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Faktur berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update faktur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui faktur.');
        }
    }

    // --- Kode Lainnya (store, create, dll.) tetap sama ---

    public function createStep1()
    {
        $suppliers = Supplier::all();
        return view('invoices.create-step1', compact('suppliers'));
    }

    public function postStep1(Request $request)
    {
        $request->validate(['supplier_id' => 'required|exists:suppliers,id']);
        $request->session()->put('supplier_id_for_invoice', $request->input('supplier_id'));
        return redirect()->route('invoices.create.show_step2');
    }

    public function showStep2(Request $request)
    {
        $supplier_id = $request->session()->get('supplier_id_for_invoice');
        if (!$supplier_id) {
            return redirect()->route('invoices.create.step1')->with('error', 'Silakan pilih supplier terlebih dahulu.');
        }
        $supplier = Supplier::findOrFail($supplier_id);
        return view('invoices.create-step2', compact('supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
            'po_number' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'received_date' => 'required|date',
            'due_date' => 'nullable|date|required_if:payment_type,kredit|after_or_equal:invoice_date',
            'payment_type' => 'required|in:debit,kredit',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
            'other_taxes' => 'nullable|array',
            'other_taxes.*.name' => 'nullable|string|max:255',
            'other_taxes.*.type' => 'nullable|in:fixed,percentage',
            'other_taxes.*.value' => 'nullable|numeric|min:0',
            'reference_images' => 'required|array|min:1',
            'reference_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5000',
        ]);
        
        $supplier_id = $request->session()->get('supplier_id_for_invoice');
        if (!$supplier_id) {
            return redirect()->route('invoices.create.step1')->with('error', 'Sesi supplier tidak ditemukan. Silakan mulai lagi.');
        }

        DB::beginTransaction();
        try {
            // Perhitungan
            $subtotalItems = 0;
            foreach ($request->input('items', []) as $item) {
                if (is_array($item) && isset($item['quantity'], $item['price'])) {
                    $subtotalItems += ((float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0));
                }
            }
            $discountValue = (float)($request->input('discount_value') ?? 0);
            $discountAmount = ($request->input('discount_type') === 'percentage') ? ($subtotalItems * $discountValue) / 100 : $discountValue;
            $baseForTax = $subtotalItems - $discountAmount;
            $ppnPercentage = (float)($request->input('ppn_percentage') ?? 0);
            $ppnAmount = ($baseForTax * $ppnPercentage) / 100;
            $otherTaxesList = [];
            $totalOtherTaxesAmount = 0;
            $otherTaxesInput = $request->input('other_taxes', []);
            if (is_array($otherTaxesInput)) {
                foreach ($otherTaxesInput as $tax) {
                    if (is_array($tax) && !empty($tax['name']) && isset($tax['value'], $tax['type'])) {
                        $taxValue = (float)$tax['value'];
                        $taxAmount = ($tax['type'] === 'percentage') ? ($baseForTax * $taxValue) / 100 : $taxValue;
                        $otherTaxesList[] = ['name' => $tax['name'], 'type' => $tax['type'], 'value' => $taxValue, 'amount' => $taxAmount];
                        $totalOtherTaxesAmount += $taxAmount;
                    }
                }
            }
            $totalAmount = $baseForTax + $ppnAmount + $totalOtherTaxesAmount;

            $invoice = Invoice::create([
                'supplier_id' => $supplier_id,
                'invoice_number' => $request->input('invoice_number'),
                'po_number' => $request->input('po_number'),
                'invoice_date' => $request->input('invoice_date'),
                'received_date' => $request->input('received_date'),
                'due_date' => $request->input('payment_type') === 'kredit' ? $request->input('due_date') : null,
                'payment_type' => $request->input('payment_type'),
                'subtotal_items' => $subtotalItems,
                'discount_type' => $request->input('discount_type'),
                'discount_value' => $discountValue,
                'ppn_percentage' => $ppnPercentage,
                'ppn_amount' => $ppnAmount,
                'other_taxes' => $otherTaxesList,
                'total_amount' => $totalAmount,
                'is_paid' => false,
                'public_code' => Str::random(32),
            ]);

            foreach ($request->input('items', []) as $item) {
                 if (is_array($item)) {
                     $invoice->invoiceItems()->create([
                         'item_name' => $item['item_name'],
                         'quantity' => $item['quantity'],
                         'unit' => $item['unit'],
                         'price' => $item['price'],
                         'subtotal' => (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0),
                     ]);
                 }
            }
            
            if ($request->hasFile('reference_images')) {
                foreach ($request->file('reference_images') as $image) {
                    if($image && $image->isValid()){
                        $path = $image->store('invoice_images/' . $invoice->id . '/references', 'public');
                        $invoice->invoiceImages()->create([
                            'filename' => $image->hashName(),
                            'filepath' => Storage::url($path),
                            'title'    => $image->getClientOriginalName(),
                            'type'     => 'reference'
                        ]);
                    }
                }
            }

            $request->session()->forget('supplier_id_for_invoice');
            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Faktur berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan faktur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan faktur: ' . $e->getMessage());
        }
    }

    public function markPaid(Invoice $invoice)
    {
        if ($invoice->paymentProofImages()->count() == 0) {
            return back()->with('error', 'Gagal. Anda harus mengunggah setidaknya satu bukti pembayaran sebelum menandai faktur sebagai lunas.');
        }
        $invoice->update(['is_paid' => true]);
        return back()->with('success', 'Faktur berhasil ditandai lunas.');
    }

    public function unmarkPaid(Invoice $invoice)
    {
        if (Auth::user()->role !== 'manager') {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }
        
        $invoice->update(['is_paid' => false]);
        return back()->with('success', 'Pelunasan faktur berhasil dibatalkan.');
    }
    
    public function destroy(Invoice $invoice)
    {
        return back()->with('error', 'Fitur hapus faktur telah dinonaktifkan.');
    }

    public function uploadPaymentProof(Request $request, Invoice $invoice)
    {
        if ($invoice->is_paid) {
            return back()->with('error', 'Tidak dapat mengubah faktur yang sudah lunas.');
        }
        
        $request->validate([
            'payment_proof_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
            'title' => 'nullable|string|max:255'
        ]);

        if ($request->hasFile('payment_proof_image')) {
            $image = $request->file('payment_proof_image');
            $path = $image->store('invoice_images/' . $invoice->id . '/payment_proofs', 'public');

            $invoice->invoiceImages()->create([
                'filename' => $image->hashName(),
                'filepath' => Storage::url($path),
                'title'    => $request->title ?? 'Bukti Pembayaran',
                'type'     => 'payment_proof'
            ]);
            return back()->with('success', 'Bukti pembayaran berhasil diunggah.');
        }
        return back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }

    public function destroyImage(InvoiceImage $image)
    {
        try {
            $storagePath = str_replace('/storage/', '', $image->filepath);

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }
            $image->delete();
            return back()->with('success', 'Gambar faktur berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus gambar faktur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus gambar.');
        }
    }
}
