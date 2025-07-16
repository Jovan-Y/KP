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

    //menampilkan halaman utama yang berisi daftar semua faktur
    public function index()
    {
        //ambil semua faktur, sertakan data supplier-nya, urutkan dari yang terbaru dengan paginate
        $invoices = Invoice::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        // kirim data faktur ke tampilan invoices index
        return view('invoices.index', compact('invoices'));
    }

    //menampilkan halaman memilih supplier sebelum membuat faktur
    public function createStep1()
    {
        $suppliers = Supplier::all(); //ambil semua data supplier
        return view('invoices.create-step1', compact('suppliers')); //data supplier ke halaman
    }

    //memproses pilihan supplier dari langkah 1
    public function postStep1(Request $request)
    {
        //pastikan pengguna sudah memilih supplier
        $request->validate(
            ['supplier_id' => 'required|exists:suppliers,id'],
            ['supplier_id.required' => 'Anda harus memilih supplier terlebih dahulu.']
        );
        //simpan id supplier yang dipilih ke dalam session
        $request->session()->put('supplier_id_for_invoice', $request->input('supplier_id'));

        //arahkan pengguna ke langkah kedua
        return redirect()->route('invoices.create.show_step2');
    }

    //menampilkan halaman kedua untuk membuat faktur: mengisi detail faktur
    public function showStep2(Request $request)
    {
        //ambil id supplier dari session.
        $supplier_id = $request->session()->get('supplier_id_for_invoice');
        // Jika tidak ada , kembalikan ke langkah 1.
        if (!$supplier_id) {
            return redirect()->route('invoices.create.step1')->with('error', 'Silakan pilih supplier terlebih dahulu.');
        }
        // Cari data supplier berdasarkan ID dan tampilkan form detail faktur.
        $supplier = Supplier::findOrFail($supplier_id);
        return view('invoices.create-step2', compact('supplier'));
    }

    //menyimpan faktur baru ke database 
    public function store(Request $request)
    {
        // 1. validasi semua input dari form.
        $validatedData = $request->validate([
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
        ], [
            // pesan error kustom
            'invoice_number.required' => 'Nomor faktur harus diisi.',
            'invoice_number.unique' => 'Nomor faktur ini sudah terdaftar.',
            'invoice_date.required' => 'Tanggal faktur harus diisi.',
            'received_date.required' => 'Tanggal diterima harus diisi.',
            'due_date.required_if' => 'Tanggal jatuh tempo harus diisi untuk pembayaran kredit.',
            'items.required' => 'Faktur harus memiliki setidaknya satu barang.',
            'items.*.item_name.required' => 'Nama barang wajib diisi.',
            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.unit.required' => 'Satuan barang wajib diisi.',
            'items.*.price.required' => 'Harga barang wajib diisi.',
            'reference_images.required' => 'Anda wajib mengunggah minimal satu gambar faktur.',
            'reference_images.*.image' => 'File yang diunggah harus berupa gambar.',
            'reference_images.*.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'reference_images.*.max' => 'Ukuran gambar maksimal 5MB.',
        ]);
        
        //ambil id supplier dari session.
        $supplier_id = $request->session()->get('supplier_id_for_invoice');
        if (!$supplier_id) {
            return redirect()->route('invoices.create.step1')->with('error', 'Sesi supplier tidak ditemukan. Silakan mulai lagi.');
        }

        //mulai transaksi database
        DB::beginTransaction();
        try {
            //2. lakukan semua perhitungan
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

            // 3. simpan data utama faktur ke tabel invoices
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

            // 4. simpan setiap item barang ke tabel invoice_items
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
            
            // 5. unggah dan simpan setiap gambar referensi
            if ($request->hasFile('reference_images')) {
                foreach ($request->file('reference_images') as $image) {
                    if($image && $image->isValid()){
                        //simpan file ke storage dan catat path-nya di database
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

            // 6. jika semua berhasil, hapus session dan komit
            $request->session()->forget('supplier_id_for_invoice');
            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Faktur berhasil ditambahkan!');

        } catch (\Exception $e) {
            // 7. jika ada error batalkan perubahan
            DB::rollBack();
            Log::error('Gagal menambahkan faktur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan faktur: ' . $e->getMessage());
        }
    }

    //Menampilkan halaman detail untuk satu faktur.
    public function show(Invoice $invoice)
    {
        //ambil data faktur beserta relasinya
        $invoice->load('supplier', 'invoiceItems', 'paymentProofImages', 'referenceImages');
        return view('invoices.show', compact('invoice'));
    }

    //menampilkan edit faktur
    public function edit(Invoice $invoice)
    {
        //faktur yang sudah lunas tidak boleh diedit.
        if ($invoice->is_paid) {
            return redirect()->route('invoices.show', $invoice->id)->with('error', 'Faktur yang sudah lunas tidak dapat diedit.');
        }
        //return view
        $invoice->load('invoiceItems', 'referenceImages');
        return view('invoices.edit', compact('invoice'));
    }

    //fungsi memperbarui data faktur
    public function update(Request $request, Invoice $invoice)
    {
        //faktur lunas tidak boleh diubah
        if ($invoice->is_paid) {
            return redirect()->route('invoices.show', $invoice->id)->with('error', 'Faktur yang sudah lunas tidak dapat diubah.');
        }

        //validasi data dan pesan error kostum
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
        ],[
            'invoice_number.required' => 'Nomor faktur wajib diisi.',
            'invoice_date.required' => 'Tanggal faktur wajib diisi.',
            'received_date.required' => 'Tanggal diterima wajib diisi.',
            'due_date.required_if' => 'Jatuh tempo wajib diisi untuk pembayaran kredit.',
            'items.required' => 'Faktur harus memiliki setidaknya satu barang.',
            'items.*.item_name.required' => 'Nama barang wajib diisi.',
            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.unit.required' => 'Satuan barang wajib diisi.',
            'items.*.price.required' => 'Harga barang wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            //hapus gambar yang ditandai
            if ($request->filled('images_to_delete')) {
                $imageIds = explode(',', $request->input('images_to_delete'));
                $imagesToDelete = InvoiceImage::whereIn('id', $imageIds)->get();
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete(str_replace('/storage', '', $image->filepath));
                    $image->delete();
                }
            }

            //unggah gambar baru (jika ada)
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
            
            //hapus semua item lama, lalu buat ulang dengan data baru
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

            // hitung ulang semua total
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

            // update data faktur
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

    //menandai faktur sebagai lunas
    public function markPaid(Request $request, Invoice $invoice)
    {
        // jika sudah lunas yaudah 
        if ($invoice->is_paid) {
            return back();
        }

        //wajib unggah bukti pembayaran sebelum menandai lunas
        if ($invoice->paymentProofImages()->count() == 0 && !$request->hasFile('payment_proof_image')) {
            return back()->with('error', 'Gagal. Anda harus mengunggah bukti pembayaran sebelum menandai faktur sebagai lunas.');
        }

        //proses dulu gambar bukti pembayaran
        if($request->hasFile('payment_proof_image')) {
            $request->validate([
                'payment_proof_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
                'title' => 'nullable|string|max:255'
            ],[
                'payment_proof_image.required' => 'Anda harus memilih file gambar untuk diunggah.',
                'payment_proof_image.image' => 'File yang diunggah harus berupa gambar.',
                'payment_proof_image.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
                'payment_proof_image.max' => 'Ukuran gambar maksimal 5MB.',
            ]);

            $image = $request->file('payment_proof_image');
            $path = $image->store('invoice_images/' . $invoice->id . '/payment_proofs', 'public');

            $invoice->invoiceImages()->create([
                'filename' => $image->hashName(),
                'filepath' => Storage::url($path),
                'title'    => $request->title ?? 'Bukti Pembayaran',
                'type'     => 'payment_proof'
            ]);
        }
        
        // kalo valid semua maka tandai lunas
        $invoice->update(['is_paid' => true]);
        return back()->with('success', 'Faktur berhasil ditandai lunas.');
    }

    //membatalkan status lunas
    public function unmarkPaid(Invoice $invoice)
    {        
        $invoice->update(['is_paid' => false]);
        return back()->with('success', 'Pelunasan faktur berhasil dibatalkan.');
    }

    //upload gambar bukti pembayaran
    public function uploadPaymentProof(Request $request, Invoice $invoice)
    {
        // tidak bisa dilakukan jika faktur sudah lunas
        if ($invoice->is_paid) {
            return back()->with('error', 'Tidak dapat mengubah faktur yang sudah lunas.');
        }
        
        // validasi
        $request->validate([
            'payment_proof_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
            'title' => 'nullable|string|max:255'
        ],[
            'payment_proof_image.required' => 'Anda harus memilih file gambar untuk diunggah.',
            'payment_proof_image.image' => 'File yang diunggah harus berupa gambar.',
            'payment_proof_image.mimes' => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'payment_proof_image.max' => 'Ukuran gambar maksimal 5MB.',
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

    //menghapus gambar 
    public function destroyImage(InvoiceImage $image)
    {
        try {
            //hapus file dari server (storage)
            $storagePath = str_replace('/storage/', '', $image->filepath);

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }
            //hapus gambar dari database.
            $image->delete();
            return back()->with('success', 'Gambar faktur berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus gambar faktur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus gambar.');
        }
    }
}
