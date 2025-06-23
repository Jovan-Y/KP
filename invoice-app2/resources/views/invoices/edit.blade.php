<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Faktur #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Input tersembunyi untuk menyimpan ID gambar yang akan dihapus --}}
                <input type="hidden" name="images_to_delete" id="images_to_delete_input">

                {{-- BAGIAN 1: DETAIL FAKTUAL & ITEM --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-start">
                             <div>
                                <h3 class="text-xl font-bold">FAKTUR</h3>
                                <div class="mt-2">
                                    <x-input-label for="invoice_number" value="Nomor Faktur" />
                                    <x-text-input id="invoice_number" name="invoice_number" type="text" class="mt-1 block w-full" :value="old('invoice_number', $invoice->invoice_number)" required />
                                </div>
                                <div class="mt-2">
                                    <x-input-label for="po_number" value="Nomor PO (Opsional)" />
                                    <x-text-input id="po_number" name="po_number" type="text" class="mt-1 block w-full" :value="old('po_number', $invoice->po_number)" />
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-lg">{{ $invoice->supplier->company_name }}</p>
                                <p class="text-sm text-gray-600">Supplier: {{ $invoice->supplier->name }}</p>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm mb-6">
                            <div>
                                <x-input-label for="invoice_date" value="Tanggal Faktur" />
                                <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" :value="old('invoice_date', $invoice->invoice_date->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label for="received_date" value="Tanggal Diterima" />
                                <x-text-input id="received_date" name="received_date" type="date" class="mt-1 block w-full" :value="old('received_date', $invoice->received_date->format('Y-m-d'))" required />
                            </div>
                            @if($invoice->payment_type === 'kredit')
                                <div>
                                    <x-input-label for="due_date" value="Jatuh Tempo" />
                                    <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '')" />
                                </div>
                            @endif
                            <div>
                                <strong>Pembayaran:</strong><br>{{ ucfirst($invoice->payment_type) }}
                            </div>
                        </div>

                        <h4 class="text-lg font-bold mt-6 mb-2">Rincian</h4>
                        <div class="overflow-x-auto">
                             <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(old('items', $invoice->invoiceItems->toArray()) as $index => $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <x-text-input type="text" name="items[{{ $index }}][item_name]" class="w-full" :value="$item['item_name']" required />
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <x-text-input type="number" name="items[{{ $index }}][quantity]" class="w-full text-right" :value="$item['quantity']" required />
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <x-text-input type="text" name="items[{{ $index }}][unit]" class="w-full text-right" :value="$item['unit']" required />
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <x-text-input type="number" step="0.01" name="items[{{ $index }}][price]" class="w-full text-right" :value="$item['price']" required />
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                             <p class="text-sm text-gray-500 mt-2">* Menambah atau menghapus item saat edit belum didukung. Silakan ubah data yang ada.</p>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN GAMBAR FAKTUR (REFERENSI) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                    <div class="p-6">
                        <h4 class="text-lg font-bold mb-4 text-gray-900">Gambar Faktur</h4>
                        @if($invoice->referenceImages->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                @foreach($invoice->referenceImages as $image)
                                    <div class="relative group border rounded-lg overflow-hidden shadow-sm">
                                        <a href="{{ $image->filepath }}" target="_blank">
                                            <img src="{{ $image->filepath }}" alt="{{ $image->title ?? $image->filename }}" class="w-full h-32 object-cover">
                                        </a>
                                        {{-- PERUBAHAN: Overlay disempurnakan dengan efek hover --}}
                                        <div onclick="unmarkImageForDeletion(this)" data-image-id="{{ $image->id }}" class="absolute inset-0 bg-red-600 bg-opacity-70 flex items-center justify-center text-white hidden cursor-pointer group" title="Batal Hapus">
                                             <div class="flex flex-col items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-xs font-bold mt-1">Batal Hapus?</span>
                                            </div>
                                        </div>
                                        <div class="absolute top-1 right-1">
                                            <button type="button" data-image-id="{{ $image->id }}" onclick="markImageForDeletion(this)" class="bg-red-500 text-white rounded-full p-1.5 leading-none hover:bg-red-600 transition-opacity opacity-0 group-hover:opacity-100" title="Tandai untuk dihapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Tidak ada gambar referensi.</p>
                        @endif
                        
                        <div class="mt-6 border-t pt-4">
                            <h3 class="text-md font-semibold mb-2">Tambah Gambar Faktur Baru</h3>
                            <div id="reference_images_container_edit" class="space-y-4">
                            </div>
                            <button type="button" onclick="addReferenceImageEdit()" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                Tambah File Gambar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                    <div class="p-6 flex justify-end items-center space-x-4">
                         <a href="{{ route('invoices.show', $invoice->id) }}" class="text-gray-600 hover:text-gray-900">Batal</a>
                         <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let imageEditIndex = 0;

        document.addEventListener('DOMContentLoaded', () => {
            addReferenceImageEdit(); 
        });

        function addReferenceImageEdit() {
            const container = document.getElementById('reference_images_container_edit');
            const newImageRow = document.createElement('div');
            newImageRow.id = `image_edit_row_${imageEditIndex}`;
            newImageRow.className = 'flex items-center gap-2';
            newImageRow.innerHTML = `
                <input type="file" name="reference_images[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" multiple>
                <button type="button" onclick="removeReferenceImageEdit(${imageEditIndex})" class="text-red-500 hover:text-red-700 font-bold p-1">&times;</button>
            `;
            container.appendChild(newImageRow);
            imageEditIndex++;
        }
        
        function removeReferenceImageEdit(index) {
            const row = document.getElementById(`image_edit_row_${index}`);
            row.remove();
        }

        function markImageForDeletion(button) {
            const imageId = button.getAttribute('data-image-id');
            const deleteInput = document.getElementById('images_to_delete_input');
            let ids = deleteInput.value ? deleteInput.value.split(',') : [];

            if (!ids.includes(String(imageId))) {
                ids.push(imageId);
            }
            deleteInput.value = ids.join(',');

            const imageCard = button.closest('.relative.group');
            if (imageCard) {
                const overlay = imageCard.querySelector('.absolute.inset-0');
                if(overlay) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }
                button.style.display = 'none';
            }
        }
        
        function unmarkImageForDeletion(overlay) {
            const imageId = overlay.getAttribute('data-image-id');
            const deleteInput = document.getElementById('images_to_delete_input');
            let ids = deleteInput.value ? deleteInput.value.split(',') : [];

            const index = ids.indexOf(String(imageId));
            if (index > -1) {
                ids.splice(index, 1);
            }
            deleteInput.value = ids.join(',');

            const imageCard = overlay.closest('.relative.group');
            if (imageCard) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                
                const deleteButton = imageCard.querySelector('button[data-image-id]');
                if(deleteButton) {
                    deleteButton.style.display = 'block';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
