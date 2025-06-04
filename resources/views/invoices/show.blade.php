<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Faktur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Faktur #{{ $invoice->invoice_number }}</h3>
                    <p class="text-sm text-gray-600">Supplier: {{ $invoice->supplier->name }}</p>
                    <hr class="my-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Nama Faktur:</strong> {{ $invoice->invoice_name }}</p>
                            <p><strong>Nomor Faktur:</strong> {{ $invoice->invoice_number }}</p>
                            <p><strong>Tanggal Faktur:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                            <p><strong>Tanggal Diterima:</strong> {{ $invoice->received_date->format('d M Y') }}</p>
                            <p><strong>Jam:</strong> {{ $invoice->time_zone }}</p>
                            <p><strong>Metode Pembayaran:</strong> {{ ucfirst($invoice->payment_method) }}</p>
                            <p><strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                            <p><strong>Status:</strong>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $invoice->is_paid ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            @if($invoice->has_ppn)
                                <p><strong>PPN:</strong> Ya ({{ $invoice->ppn_type == 'included' ? 'Sudah termasuk' : 'Belum termasuk' }} 11%)</p>
                            @else
                                <p><strong>PPN:</strong> Tidak</p>
                            @endif
                        </div>
                    </div>

                    <h4 class="text-lg font-bold mt-6 mb-2">Daftar Barang</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->invoiceItems as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->item_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 text-right">
                        <p><strong>Subtotal Barang:</strong> Rp {{ number_format($invoice->subtotal_items, 2, ',', '.') }}</p>
                        <p><strong>Diskon:</strong> Rp {{ number_format($invoice->discount, 2, ',', '.') }}</p>
                        <p><strong>Biaya Pengiriman:</strong> Rp {{ number_format($invoice->shipping_cost, 2, ',', '.') }}</p>
                        @if($invoice->has_ppn)
                            <p><strong>PPN (11%):</strong> Rp {{ number_format($invoice->ppn_amount, 2, ',', '.') }}</p>
                        @endif
                        <p class="text-xl font-bold mt-2">Total: Rp {{ number_format($invoice->total_amount, 2, ',', '.') }}</p>
                    </div>

                    @if(Auth::user()->role === 'manager')
                    <div class="mt-8 flex space-x-4">
                        @if(!$invoice->is_paid)
                            <form action="{{ route('invoices.markPaid', $invoice->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menandai faktur ini sebagai lunas?');">
                                @csrf
                                <x-primary-button class="bg-green-600 hover:bg-green-500">Tandai sebagai Lunas</x-primary-button>
                            </form>
                        @else
                            <form action="{{ route('invoices.unmarkPaid', $invoice->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin membatalkan pelunasan faktur ini?');">
                                @csrf
                                <x-secondary-button>Batalkan Pelunasan</x-secondary-button>
                            </form>
                        @endif

                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus faktur ini? Faktur akan dipindahkan ke tempat sampah.');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>Hapus Faktur</x-danger-button>
                        </form>
                    </div>

                    <h4 class="text-lg font-bold mt-8 mb-2">Unggah Gambar Faktur</h4>
                    <form action="{{ route('invoices.uploadImage', $invoice->id) }}" method="POST" enctype="multipart/form-data" class="mb-8">
                        @csrf
                        <div class="flex items-center space-x-4">
                            <input type="file" name="invoice_image" id="invoice_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                            <x-primary-button>Unggah</x-primary-button>
                        </div>
                        @error('invoice_image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </form>
                    @endif

                    <h4 class="text-lg font-bold mt-8 mb-2">Gambar Faktur</h4>
                    <p class="text-sm text-gray-600 mb-4">Akses publik gambar faktur ini: <a href="{{ route('public.invoice.image', ['publicCode' => $invoice->public_code, 'filename' => 'example.jpg']) }}" target="_blank" class="underline text-blue-600">Link Publik</a> (Ganti `example.jpg` dengan nama file gambar yang sebenarnya)</p>
                    @if($invoice->invoiceImages->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($invoice->invoiceImages as $image)
                                <div class="border rounded-lg overflow-hidden shadow-sm">
                                    {{-- GUNAKAN Storage::url() UNTUK MEMBANGUN URL PUBLIK DARI filepath YANG DISIMPAN --}}
                                    <a href="{{ Storage::url($image->filepath) }}" target="_blank">
                                        <img src="{{ Storage::url($image->filepath) }}" alt="{{ $image->title ?? $image->filename }}" class="w-full h-48 object-cover">
                                    </a>
                                    <div class="p-2 text-xs text-gray-600">
                                        {{ $image->title ?? $image->filename }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Tidak ada gambar faktur yang diunggah.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>