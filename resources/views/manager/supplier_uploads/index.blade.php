<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Unggahan Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Unggahan Baru dari Supplier (Belum Dikaitkan)</h3>

                    @if (session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($unlinkedUploads->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($unlinkedUploads as $upload)
                                <div class="border rounded-lg overflow-hidden shadow-sm flex flex-col">
                                    <div class="flex-grow">
                                        <a href="{{ Storage::url($upload->filepath) }}" target="_blank">
                                            <img src="{{ Storage::url($upload->filepath) }}" alt="{{ $upload->title ?? $upload->filename }}" class="w-full h-48 object-cover">
                                        </a>
                                    </div>
                                    <div class="p-4 bg-gray-50 flex-none">
                                        <p class="text-sm font-semibold text-gray-800">{{ $upload->title ?? 'Tanpa Judul' }}</p>
                                        {{-- Pastikan relasi supplier ada untuk menghindari error --}}
                                        @if($upload->supplier)
                                            <p class="text-xs text-gray-600">Dari: {{ $upload->supplier->name }} ({{ $upload->supplier->email ?? 'N/A' }})</p>
                                        @endif
                                        @if($upload->supplier_reference_code)
                                            <p class="text-xs text-gray-600">Kode Ref. Supplier: {{ $upload->supplier_reference_code }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500">Diunggah: {{ $upload->created_at->format('d M Y H:i') }}</p>
                                    </div>

                                    {{-- ======================= BLOK YANG DIUBAH ======================= --}}
                                    <div class="p-4 border-t border-gray-200 bg-white">

                                        {{-- Cek apakah ada faktur yang relevan untuk unggahan ini --}}
                                        @if(isset($invoicesBySupplier[$upload->supplier_id]) && $invoicesBySupplier[$upload->supplier_id]->isNotEmpty())
                                            
                                            {{-- Jika ada, tampilkan form dengan dropdown yang sudah difilter --}}
                                            <form action="{{ route('manager.supplier_uploads.link', $upload->id) }}" method="POST" class="space-y-2">
                                                @csrf
                                                <x-input-label for="invoice_id_{{ $upload->id }}" :value="__('Kaitkan ke Faktur')" />
                                                <select id="invoice_id_{{ $upload->id }}" name="invoice_id" class="block w-full border-gray-300 rounded-md shadow-sm" required>
                                                    <option value="">-- Pilih Faktur --</option>
                                                    
                                                    {{-- Loop hanya untuk faktur dari supplier yang benar --}}
                                                    @foreach($invoicesBySupplier[$upload->supplier_id] as $invoice)
                                                        <option value="{{ $invoice->id }}">
                                                            {{ $invoice->invoice_number }} - {{ $invoice->invoice_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('invoice_id')" class="mt-2" />
                                                <x-primary-button class="w-full justify-center mt-2">Kaitkan</x-primary-button>
                                            </form>

                                        @else
                                            {{-- Jika tidak ada faktur, tampilkan pesan --}}
                                            <div class="text-sm text-gray-500 text-center p-4">
                                                Tidak ada faktur dengan supplier yang sama.
                                            </div>
                                        @endif

                                        {{-- Form untuk hapus tetap ada di bawah --}}
                                        <form action="{{ route('manager.supplier_uploads.destroy', $upload->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus unggahan ini?');" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button class="w-full justify-center">Hapus</x-danger-button>
                                        </form>
                                    </div>
                                    {{-- ===================== AKHIR BLOK YANG DIUBAH ===================== --}}

                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $unlinkedUploads->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">Tidak ada unggahan baru dari supplier saat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>