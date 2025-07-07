<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Faktur #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('success')) <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert"><p>{{ session('success') }}</p></div> @endif
            @if (session('error')) <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert"><p>{{ session('error') }}</p></div> @endif

            {{-- BAGIAN 1: DETAIL FAKTUAL & ITEM --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold">FAKTUR</h3>
                            <p class="text-gray-500">{{ $invoice->invoice_number }}</p>
                            @if($invoice->po_number)
                                <p class="text-sm text-gray-500 mt-1">Nomor PO: {{ $invoice->po_number }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-lg">{{ $invoice->supplier->company_name }}</p>
                            <p class="text-sm text-gray-600">Supplier: {{ $invoice->supplier->name }}</p>
                            @if($invoice->supplier->address)
                                <p class="text-sm text-gray-600">{{ $invoice->supplier->address }}</p>
                            @endif
                            <p class="text-sm text-gray-600">{{ $invoice->supplier->phone }}</p>
                            
                            @if(!empty($invoice->supplier->payment_details))
                                <div class="mt-2 pt-2 border-t border-gray-200 text-left">
                                    <p class="font-semibold text-xs text-gray-500 uppercase">Info Pembayaran:</p>
                                    @foreach($invoice->supplier->payment_details as $detail)
                                        <p class="text-sm text-gray-600">
                                            {{ $detail['bank_name'] ?? 'N/A' }}: <strong>{{ $detail['account_number'] ?? 'N/A' }}</strong> (a/n {{ $detail['account_name'] ?? 'N/A' }})
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm mb-6">
                        <div><strong>Tanggal Faktur:</strong><br>{{ $invoice->invoice_date->format('d M Y') }}</div>
                        <div><strong>Tanggal Diterima:</strong><br>{{ $invoice->received_date->format('d M Y') }}</div>
                        @if($invoice->payment_type === 'kredit' && $invoice->due_date)
                            <div><strong>Jatuh Tempo:</strong><br>{{ $invoice->due_date->format('d M Y') }}</div>
                        @endif
                        <div><strong>Pembayaran:</strong><br>{{ ucfirst($invoice->payment_type) }}</div>
                    </div>

                    <h4 class="text-lg font-bold mt-6 mb-2">Rincian</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->invoiceItems as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->item_name }}</td>
                                        <td class="px-6 py-4 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-white">
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right font-semibold">Subtotal</td>
                                    <td class="px-6 py-2 text-right">Rp {{ number_format($invoice->subtotal_items, 2, ',', '.') }}</td>
                                </tr>
                                @if($invoice->discount_value > 0)
                                    @php
                                        $discountAmount = $invoice->discount_type === 'percentage'
                                            ? ($invoice->subtotal_items * $invoice->discount_value) / 100
                                            : $invoice->discount_value;
                                        $discountLabel = $invoice->discount_type === 'percentage'
                                            ? $invoice->discount_value . '%'
                                            : 'Rp ' . number_format($invoice->discount_value, 0, ',', '.');
                                    @endphp
                                    <tr>
                                        <td colspan="3" class="px-6 py-2 text-right font-semibold">Diskon ({{ $discountLabel }})</td>
                                        <td class="px-6 py-2 text-right text-red-600">- Rp {{ number_format($discountAmount, 2, ',', '.') }}</td>
                                    </tr>
                                @endif
                                @php $baseForTax = $invoice->subtotal_items - ($discountAmount ?? 0); @endphp
                                <tr class="font-semibold border-t">
                                    <td colspan="3" class="px-6 py-2 text-right">Dasar Pengenaan Pajak</td>
                                    <td class="px-6 py-2 text-right">Rp {{ number_format($baseForTax, 2, ',', '.') }}</td>
                                </tr>
                                @if($invoice->ppn_percentage > 0)
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right">PPN ({{ $invoice->ppn_percentage }}%)</td>
                                    <td class="px-6 py-2 text-right">Rp {{ number_format($invoice->ppn_amount, 2, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($invoice->other_taxes)
                                    @foreach($invoice->other_taxes as $tax)
                                        @php
                                            $taxLabel = $tax['type'] === 'percentage'
                                                ? $tax['value'] . '%'
                                                : 'Rp ' . number_format($tax['value'], 0, ',', '.');
                                        @endphp
                                        <tr>
                                            <td colspan="3" class="px-6 py-2 text-right">{{ $tax['name'] }} ({{ $taxLabel }})</td>
                                            <td class="px-6 py-2 text-right">Rp {{ number_format($tax['amount'], 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr class="bg-gray-100 font-bold text-lg">
                                    <td colspan="3" class="px-6 py-3 text-right">TOTAL</td>
                                    <td class="px-6 py-3 text-right">Rp {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: TOMBOL AKSI --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex items-center space-x-4">
                    @if (!$invoice->is_paid)
                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Faktur
                        </a>
                    @endif

                    @php
                        // Cek bukti bayar dengan relasi model
                        $hasPaymentProof = $invoice->paymentProofImages->isNotEmpty();
                    @endphp

                    @if (!$invoice->is_paid && $hasPaymentProof)
                        <form action="{{ route('invoices.markPaid', $invoice->id) }}" method="POST" onsubmit="return confirm('Tandai faktur ini sebagai lunas?');">
                            @csrf
                            @method('PATCH')
                            <x-primary-button type="submit" class="bg-green-600 hover:bg-green-500">
                                Tandai Lunas
                            </x-primary-button>
                        </form>
                    @elseif (!$invoice->is_paid && !$hasPaymentProof)
                        <p class="text-sm text-gray-500 italic">Unggah bukti pembayaran untuk melunasi faktur.</p>
                    @endif
                    
                    @if ($invoice->is_paid && Auth::user()->role === 'manager')
                        <form action="{{ route('invoices.unmarkPaid', $invoice->id) }}" method="POST" onsubmit="return confirm('Batalkan pelunasan faktur ini?');">
                            @csrf
                            @method('PATCH')
                            <x-secondary-button type="submit">
                                Batalkan Pelunasan
                            </x-secondary-button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- BAGIAN 3: GAMBAR-GAMBAR --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- KOLOM KIRI: KHUSUS BUKTI PEMBAYARAN --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-bold mb-4 text-gray-900">Bukti Pembayaran</h4>
                        @if (!$invoice->is_paid)
                            <div class="mb-6 p-4 border rounded-md bg-gray-50">
                                <form action="{{ route('invoices.uploadPaymentProof', $invoice->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-2">
                                        <x-input-label for="title_payment" value="Judul/Keterangan (Opsional)" />
                                        <x-text-input id="title_payment" name="title" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_proof_image" value="Pilih File Bukti Bayar" />
                                        <input type="file" name="payment_proof_image" id="payment_proof_image" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                        @error('payment_proof_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <x-primary-button class="mt-4">Unggah Bukti</x-primary-button>
                                </form>
                            </div>
                        @endif

                        @if($invoice->paymentProofImages->isNotEmpty())
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($invoice->paymentProofImages as $image)
                                    <div class="border rounded-lg overflow-hidden shadow-sm">
                                        <a href="{{ $image->filepath }}" target="_blank">
                                            <img src="{{ $image->filepath }}" alt="{{ $image->title ?? $image->filename }}" class="w-full h-32 object-cover">
                                        </a>
                                        <div class="p-2 text-xs text-gray-600 flex justify-between items-center">
                                            <p class="truncate pr-2" title="{{ $image->title ?? $image->filename }}">{{ $image->title ?? $image->filename }}</p>
                                            @if(Auth::user()->role === 'manager' && !$invoice->is_paid)
                                                <form action="{{ route('invoices.images.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Hapus bukti pembayaran ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold flex-shrink-0">&times;</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Belum ada bukti pembayaran.</p>
                        @endif
                    </div>
                </div>

                {{-- KHUSUS GAMBAR REFERENSI --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                         <h4 class="text-lg font-bold mb-4 text-gray-900">Gambar Faktur</h4>
                         @if($invoice->referenceImages->isNotEmpty())
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($invoice->referenceImages as $image)
                                     <div class="border rounded-lg overflow-hidden shadow-sm">
                                         <a href="{{ $image->filepath }}" target="_blank">
                                             <img src="{{ $image->filepath }}" alt="{{ $image->title ?? $image->filename }}" class="w-full h-32 object-cover">
                                         </a>
                                         <div class="p-2 text-xs text-gray-600">
                                             <p class="truncate" title="{{ $image->title ?? $image->filename }}">{{ $image->title ?? $image->filename }}</p>
                                         </div>
                                     </div>
                                @endforeach
                            </div>
                         @else
                             <p class="text-gray-500 text-center py-4">Tidak ada gambar referensi.</p>
                         @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
