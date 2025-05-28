<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Faktur - Detail Faktur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Supplier: {{ $supplier->name }}</h3>
                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <x-input-label for="invoice_name" :value="__('Nama Faktur')" />
                                    <x-text-input id="invoice_name" class="block mt-1 w-full" type="text" name="invoice_name" :value="old('invoice_name')" required autofocus />
                                    <x-input-error :messages="$errors->get('invoice_name')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="invoice_number" :value="__('Nomor Faktur')" />
                                    <x-text-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number')" required />
                                    <x-input-error :messages="$errors->get('invoice_number')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="invoice_date" :value="__('Tanggal Faktur')" />
                                    <x-text-input id="invoice_date" class="block mt-1 w-full" type="date" name="invoice_date" :value="old('invoice_date')" required />
                                    <x-input-error :messages="$errors->get('invoice_date')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="received_date" :value="__('Tanggal Diterima')" />
                                    <x-text-input id="received_date" class="block mt-1 w-full" type="date" name="received_date" :value="old('received_date')" required />
                                    <x-input-error :messages="$errors->get('received_date')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <x-input-label for="time_zone" :value="__('Jam')" />
                                    <select id="time_zone" name="time_zone" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="WIB" {{ old('time_zone') == 'WIB' ? 'selected' : '' }}>WIB</option>
                                        <option value="WIT" {{ old('time_zone') == 'WIT' ? 'selected' : '' }}>WIT</option>
                                        <option value="WITA" {{ old('time_zone') == 'WITA' ? 'selected' : '' }}>WITA</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('time_zone')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label :value="__('Pembayaran')" />
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="payment_method" value="cash" class="form-radio" {{ old('payment_method') == 'cash' ? 'checked' : '' }} required>
                                            <span class="ml-2 text-sm text-gray-600">Cash</span>
                                        </label>
                                        <label class="inline-flex items-center ml-6">
                                            <input type="radio" name="payment_method" value="credit" class="form-radio" {{ old('payment_method') == 'credit' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Kredit</span>
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="due_date" :value="__('Jatuh Tempo')" />
                                    <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" required />
                                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="has_ppn" id="has_ppn" class="form-checkbox" onchange="togglePpnOptions()" {{ old('has_ppn') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Aktifkan PPN (11%)</span>
                                    </label>
                                    <div id="ppn_options" class="mt-2 {{ old('has_ppn') ? '' : 'hidden' }}">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="ppn_type" value="included" class="form-radio" {{ old('ppn_type') == 'included' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Harga barang sudah termasuk 11%</span>
                                        </label>
                                        <label class="inline-flex items-center ml-6">
                                            <input type="radio" name="ppn_type" value="excluded" class="form-radio" {{ old('ppn_type') == 'excluded' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Harga barang belum termasuk 11%</span>
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('ppn_type')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold mt-6 mb-4">Daftar Barang</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="items_table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if(old('items'))
                                        @foreach(old('items') as $index => $item)
                                            <tr id="item_row_{{ $index }}">
                                                <td class="px-6 py-4 whitespace-nowrap"><x-text-input type="text" name="items[{{ $index }}][item_name]" value="{{ $item['item_name'] }}" required /></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-text-input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="1" oninput="calculateSubtotal({{ $index }})" required /></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-text-input type="text" name="items[{{ $index }}][unit]" value="{{ $item['unit'] }}" required /></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><x-text-input type="number" name="items[{{ $index }}][price]" value="{{ $item['price'] }}" step="0.01" min="0" oninput="calculateSubtotal({{ $index }})" required /></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span id="subtotal_{{ $index }}">Rp {{ number_format($item['quantity'] * $item['price'], 2, ',', '.') }}</span></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button type="button" onclick="removeItem({{ $index }})" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-plus mr-2"></i> Tambah Barang
                            </button>
                        </div>

                        <h3 class="text-lg font-bold mt-6 mb-4">Perhitungan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><strong>Subtotal Barang:</strong> <span id="display_subtotal_items">Rp 0,00</span></p>
                                <div class="mb-4 mt-2">
                                    <x-input-label for="discount" :value="__('Diskon')" />
                                    <x-text-input id="discount" class="block mt-1 w-full" type="number" name="discount" :value="old('discount', 0)" min="0" oninput="calculateTotal()" />
                                    <x-input-error :messages="$errors->get('discount')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <p><strong>PPN (11%):</strong> <span id="display_ppn_amount">Rp 0,00</span></p>
                                <div class="mb-4 mt-2">
                                    <x-input-label for="shipping_cost" :value="__('Biaya Pengiriman')" />
                                    <x-text-input id="shipping_cost" class="block mt-1 w-full" type="number" name="shipping_cost" :value="old('shipping_cost', 0)" step="0.01" min="0" oninput="calculateTotal()" />
                                    <x-input-error :messages="$errors->get('shipping_cost')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-4">
                            <p class="text-2xl font-bold">Total: <span id="display_total_amount">Rp 0,00</span></p>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Tambah Faktur') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
    // Ambil data item lama dengan aman, default ke array kosong jika tidak ada
    const oldItemsData = @json(old('items', []));
    let itemIndex = oldItemsData.length; // Mulai index dari jumlah item lama

    function addItem() {
        const tableBody = document.querySelector('#items_table tbody');
        const newRow = document.createElement('tr');
        newRow.id = `item_row_${itemIndex}`;
        newRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="text" name="items[${itemIndex}][item_name]" 
                       class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                       required />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" name="items[${itemIndex}][quantity]" 
                       class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                       min="1" oninput="calculateSubtotal(${itemIndex})" required />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="text" name="items[${itemIndex}][unit]" 
                       class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                       required />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" name="items[${itemIndex}][price]" 
                       class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                       step="0.01" min="0" oninput="calculateSubtotal(${itemIndex})" required />
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span id="subtotal_${itemIndex}">Rp 0,00</span></td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button type="button" onclick="removeItem(${itemIndex})" class="text-red-600 hover:text-red-900">Hapus</button>
            </td>
        `;
        tableBody.appendChild(newRow);
        itemIndex++;
    }

    function removeItem(index) {
        document.getElementById(`item_row_${index}`).remove();
        calculateTotal(); // Recalculate total after removing item
    }

    function calculateSubtotal(index) {
        const quantity = parseFloat(document.querySelector(`input[name="items[${index}][quantity]"]`).value) || 0;
        const price = parseFloat(document.querySelector(`input[name="items[${index}][price]"]`).value) || 0;
        const subtotal = quantity * price;
        document.getElementById(`subtotal_${index}`).textContent = `Rp ${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        calculateTotal();
    }

    function calculateTotal() {
        let subtotalItems = 0;
        document.querySelectorAll('[id^="subtotal_"]').forEach(span => {
            subtotalItems += parseFloat(span.textContent.replace('Rp ', '').replace('.', '').replace(',', '.')) || 0;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const shippingCost = parseFloat(document.getElementById('shipping_cost').value) || 0;
        let ppnAmount = 0;
        const hasPpn = document.getElementById('has_ppn').checked;
        const ppnType = document.querySelector('input[name="ppn_type"]:checked')?.value;

        let totalBeforePpn = subtotalItems - discount + shippingCost;

        if (hasPpn && ppnType === 'excluded') {
            ppnAmount = totalBeforePpn * 0.11;
        }

        const totalAmount = totalBeforePpn + ppnAmount;

        document.getElementById('display_subtotal_items').textContent = `Rp ${subtotalItems.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('display_ppn_amount').textContent = `Rp ${ppnAmount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('display_total_amount').textContent = `Rp ${totalAmount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function togglePpnOptions() {
        const ppnOptions = document.getElementById('ppn_options');
        if (document.getElementById('has_ppn').checked) {
            ppnOptions.classList.remove('hidden');
            // Pastikan salah satu radio PPN dipilih jika PPN diaktifkan
            if (!document.querySelector('input[name="ppn_type"]:checked')) {
                document.querySelector('input[name="ppn_type"][value="excluded"]').checked = true;
            }
        } else {
            ppnOptions.classList.add('hidden');
            document.querySelectorAll('input[name="ppn_type"]').forEach(radio => radio.checked = false);
        }
        calculateTotal();
    }

    // Initial calculations on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Jika ada data lama (dari validasi gagal), isi tabel
        if (oldItemsData.length > 0) {
            oldItemsData.forEach((item, index) => {
                const tableBody = document.querySelector('#items_table tbody');
                const newRow = document.createElement('tr');
                newRow.id = `item_row_${index}`;
                // Gunakan input HTML biasa, dan pastikan nilai di-escape dengan benar
                newRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="text" name="items[${index}][item_name]" 
                               value="${(item.item_name || '').replace(/"/g, '&quot;')}" 
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="items[${index}][quantity]" 
                               value="${item.quantity || ''}" 
                               min="1" oninput="calculateSubtotal(${index})" 
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="text" name="items[${index}][unit]" 
                               value="${(item.unit || '').replace(/"/g, '&quot;')}" 
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="items[${index}][price]" 
                               value="${item.price || ''}" 
                               step="0.01" min="0" oninput="calculateSubtotal(${index})" 
                               class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span id="subtotal_${index}">Rp ${((item.quantity || 0) * (item.price || 0)).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button type="button" onclick="removeItem(${index})" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                `;
                tableBody.appendChild(newRow);
                // Setelah menambahkan baris lama, lakukan perhitungan subtotal
                calculateSubtotal(index);
            });
            itemIndex = oldItemsData.length; // Perbarui itemIndex setelah menambahkan item lama
        } else {
            addItem(); // Tambahkan satu baris item awal jika tidak ada data lama
        }
        togglePpnOptions(); // Set status PPN awal
        calculateTotal(); // Pastikan total dihitung saat halaman dimuat
    });
</script>
@endpush
</x-app-layout>