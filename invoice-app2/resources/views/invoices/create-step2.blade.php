<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Faktur - Detail Faktur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Supplier: {{ $supplier->company_name }}</h3>

                    <form action="{{ route('invoices.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

                        @if (session('error'))
                            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                                <strong class="font-bold">Terjadi Kesalahan:</strong>
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif
                        @if ($errors->any() && !$errors->has('items.*') && !$errors->has('reference_images'))
                            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                                <strong class="font-bold">Ada beberapa kesalahan:</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        @if (!Str::startsWith($error, 'The items') && !Str::startsWith($error, 'The reference images'))
                                            <li>{{ $error }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div id="old-input-data"
                             data-old-items="{{ json_encode(old('items', [['item_name' => '', 'quantity' => 1, 'unit' => 'Pcs', 'price' => 0]])) }}"
                             data-old-taxes="{{ json_encode(old('other_taxes', [])) }}"
                             data-errors="{{ json_encode($errors->toArray()) }}">
                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="invoice_number" value="Nomor Faktur" />
                                <x-text-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('invoice_number')" />
                            </div>
                            <div>
                                <x-input-label for="po_number" value="Nomor PO (Opsional)" />
                                <x-text-input id="po_number" class="block mt-1 w-full" type="text" name="po_number" :value="old('po_number')" />
                                <x-input-error class="mt-2" :messages="$errors->get('po_number')" />
                            </div>
                            <div>
                                <x-input-label for="invoice_date" value="Tanggal Faktur" />
                                <x-text-input id="invoice_date" class="block mt-1 w-full" type="date" name="invoice_date" :value="old('invoice_date')" />
                                <x-input-error class="mt-2" :messages="$errors->get('invoice_date')" />
                            </div>
                            <div>
                                <x-input-label for="received_date" value="Tanggal Diterima" />
                                <x-text-input id="received_date" class="block mt-1 w-full" type="date" name="received_date" :value="old('received_date')" />
                                <x-input-error class="mt-2" :messages="$errors->get('received_date')" />
                            </div>
                            <div id="due_date_wrapper">
                                <x-input-label for="due_date" value="Jatuh Tempo" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" />
                                <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label value="Jenis Pembayaran" />
                                <div class="flex items-center space-x-4 mt-1">
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_type" value="debit" class="payment-type-radio text-indigo-600 focus:ring-indigo-500" {{ old('payment_type', 'kredit') == 'debit' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Debit</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_type" value="kredit" class="payment-type-radio text-indigo-600 focus:ring-indigo-500" {{ old('payment_type', 'kredit') == 'kredit' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Kredit</span>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('payment_type')" />
                            </div>
                        </div>

                        <h3 class="text-lg font-bold mt-8 mb-4 border-t pt-6">Daftar Barang</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full" id="items_table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                        <th class="py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white"></tbody>
                            </table>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('items')" />
                        <div class="mt-4 flex justify-end">
                            <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Tambah Barang
                            </button>
                        </div>

                        <h3 class="text-lg font-bold mt-8 mb-4 border-t pt-6">Perhitungan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <div class="mb-4">
                                    <x-input-label for="discount_value" value="Diskon" />
                                    <div class="flex gap-2">
                                        {{-- input untuk diskon --}}
                                        <select name="discount_type" id="discount_type" class="block border-gray-300 rounded-md shadow-sm" onchange="calculateTotal()">
                                            <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Rp</option>
                                            <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>%</option>
                                        </select>
                                        <x-text-input id="discount_value" class="block w-full" type="number" name="discount_value" :value="old('discount_value', 0)" min="0" oninput="calculateTotal()" />
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('discount_value')" />
                                </div>
                                {{-- input untuk PPN --}}
                                <div class="mb-4">
                                    <x-input-label for="ppn_percentage" value="PPN (%)" />
                                    <x-text-input id="ppn_percentage" class="block mt-1 w-full" type="number" name="ppn_percentage" :value="old('ppn_percentage', 11)" step="0.01" min="0" max="100" oninput="calculateTotal()" />
                                    <x-input-error class="mt-2" :messages="$errors->get('ppn_percentage')" />
                                </div>
                                {{-- input untuk pajak lainnya --}}
                                <div>
                                    <label class="font-medium text-sm text-gray-700">Pajak Lainnya</label>
                                    <div id="other_taxes_container" class="mt-2 space-y-2"></div>
                                    <button type="button" onclick="addOtherTax()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Tambah Pajak Lain</button>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="font-bold text-gray-800 mb-4">Ringkasan</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between"><span>Subtotal Barang:</span> <span id="display_subtotal_items">Rp 0,00</span></div>
                                    <div class="flex justify-between"><span>Diskon:</span> <span id="display_discount" class="text-red-600">- Rp 0,00</span></div>
                                    <hr>
                                    <div class="flex justify-between font-semibold"><span>Dasar Pengenaan Pajak:</span> <span id="display_tax_base">Rp 0,00</span></div>
                                    <hr>
                                    <div class="flex justify-between"><span>PPN (<span id="display_ppn_percentage_label">11</span>%):</span> <span id="display_ppn_amount">Rp 0,00</span></div>
                                    <div id="display_other_taxes_summary"></div>
                                    <hr>
                                    <div class="flex justify-between text-xl font-bold"><span>TOTAL AKHIR:</span> <span id="display_total_amount">Rp 0,00</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-lg font-bold mb-4">Upload Faktur (Wajib)</h3>
                            <div class="p-4 border rounded-md bg-gray-50">
                                <div id="reference_images_container" class="space-y-4">
                                    {{-- tempat input file  --}}
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('reference_images')" />
                                <button type="button" onclick="addReferenceImage()" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                    Tambah File Gambar
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <x-primary-button>
                                Simpan Faktur
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
@push('scripts')
<script>
    let itemIndex = 0;
    let taxIndex = 0;
    let imageIndex = 0;
    

    document.addEventListener('DOMContentLoaded', () => {
        const dataElement = document.getElementById('old-input-data');
        // Mengubah string JSON dari atribut 'data-old-items' menjadi objek/array JavaScript.
        // Jika atribut kosong, ia akan menjadi array kosong untuk mencegah error.
        const oldItems = JSON.parse(dataElement.dataset.oldItems || '[]');
        const oldTaxes = JSON.parse(dataElement.dataset.oldTaxes || '[]');
        const validationErrors = JSON.parse(dataElement.dataset.errors || '{}');
        //menambahkan item lama
        if (oldItems && oldItems.length > 0) {
            oldItems.forEach(itemData => addItem(itemData, validationErrors));
        } else {
            // jika tidak ada data lama
            addItem({}, validationErrors); 
        }
        if (oldTaxes && oldTaxes.length > 0) {
            oldTaxes.forEach(taxData => addOtherTax(taxData));
        }

        addReferenceImage(); 
        calculateTotal(); 

        const paymentTypeRadios = document.querySelectorAll('.payment-type-radio');
        const dueDateWrapper = document.getElementById('due_date_wrapper');
        const dueDateInput = document.getElementById('due_date');

        // fungsi untuk menampilkan atau menyembunyikan input jatuh tempo.
        function toggleDueDate() {
            const selectedRadio = document.querySelector('.payment-type-radio:checked');
            // Jika radio yang terpilih adalah debit
            if (selectedRadio && selectedRadio.value === 'debit') {
                dueDateWrapper.style.display = 'none'; 
                dueDateInput.removeAttribute('required'); 
            } else { // Jika 'kredit' atau belum dipilih
                dueDateWrapper.style.display = 'block'; 
                dueDateInput.setAttribute('required', 'required'); 
            }
        }
        paymentTypeRadios.forEach(radio => radio.addEventListener('change', toggleDueDate));
        toggleDueDate();
    });

    // fungsi bantuan untuk memformat angka menjadi format mata uang Rupiah
    function formatCurrency(value) { return `Rp ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)}`; }

    // fungsi untuk menambah baris item baru ke dalam tabel.
    // menerima parameter 'data' (untuk mengisi nilai) dan 'errors' (untuk menampilkan pesan error)
    function addItem(data = {}, errors = {}) {
        const tableBody = document.querySelector('#items_table tbody');
        const newRow = document.createElement('tr');
        const currentIndex = itemIndex; 
        newRow.id = `item_row_${currentIndex}`; 
        
        // isi baris dengan td dan input
        newRow.innerHTML = `
            <td class="px-1 py-2">
                <input type="text" name="items[${currentIndex}][item_name]" value="${data.item_name || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" />
                <div id="items.${currentIndex}.item_name_error" class="text-red-500 text-xs mt-1"></div>
            </td>
            <td class="px-1 py-2" style="width: 100px;">
                <input type="number" name="items[${currentIndex}][quantity]" value="${data.quantity || 1}" min="1" oninput="calculateSubtotal(${currentIndex})" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" />
                <div id="items.${currentIndex}.quantity_error" class="text-red-500 text-xs mt-1"></div>
            </td>
            <td class="px-1 py-2" style="width: 120px;">
                <input type="text" name="items[${currentIndex}][unit]" value="${data.unit || 'Pcs'}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" />
                <div id="items.${currentIndex}.unit_error" class="text-red-500 text-xs mt-1"></div>
            </td>
            <td class="px-1 py-2" style="width: 150px;">
                <input type="number" name="items[${currentIndex}][price]" value="${data.price || 0}" step="0.01" min="0" oninput="calculateSubtotal(${currentIndex})" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" />
                <div id="items.${currentIndex}.price_error" class="text-red-500 text-xs mt-1"></div>
            </td>
            <td class="px-1 py-2 text-sm whitespace-nowrap"><span id="subtotal_${currentIndex}">Rp 0,00</span></td>
            <td class="px-1 py-2 text-center"><button type="button" onclick="removeItem(${currentIndex})" class="text-red-500 hover:text-red-700">&times;</button></td>
        `;

        // tambahkan baris yang baru dibuat ke dalam <tbody> tabel
        tableBody.appendChild(newRow);

        // loop untuk menampilkan pesan error validasi
        ['item_name', 'quantity', 'unit', 'price'].forEach(field => {
            const errorKey = `items.${currentIndex}.${field}`;
            if (errors[errorKey]) {
                const errorElement = document.getElementById(`${errorKey}_error`);
                if (errorElement) {
                    errorElement.textContent = errors[errorKey][0];
                }
            }
        });

        calculateSubtotal(currentIndex); 
        itemIndex++;
    }

    // fungsi untuk menghapus baris item dari tabel
    function removeItem(index) { document.getElementById(`item_row_${index}`).remove(); calculateTotal(); }

    // fungsi untuk menambah baris input pajak lainnya (copas additem)
    function addOtherTax(data = {}) {
        const container = document.getElementById('other_taxes_container');
        const newTaxRow = document.createElement('div');
        newTaxRow.id = `tax_row_${taxIndex}`;
        newTaxRow.className = 'flex items-center gap-2';
        newTaxRow.innerHTML = `
            <input type="text" name="other_taxes[${taxIndex}][name]" value="${data.name || ''}" placeholder="Nama Pajak" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
            <select name="other_taxes[${taxIndex}][type]" class="block border-gray-300 rounded-md shadow-sm text-sm" onchange="calculateTotal()">
                <option value="fixed" ${ (data.type == 'fixed') ? 'selected' : '' }>Rp</option>
                <option value="percentage" ${ (data.type == 'percentage') ? 'selected' : '' }>%</option>
            </select>
            <input type="number" name="other_taxes[${taxIndex}][value]" value="${data.value || ''}" placeholder="Jumlah" step="0.01" min="0" class="mt-1 block w-32 border-gray-300 rounded-md shadow-sm text-sm" oninput="calculateTotal()">
            <button type="button" onclick="removeOtherTax(${taxIndex})" class="text-red-500 hover:text-red-700">&times;</button>
        `;
        container.appendChild(newTaxRow);
        taxIndex++;
    }

    // fungsi untuk menambah baris input file gambar
    function addReferenceImage() {
        const container = document.getElementById('reference_images_container');
        const newImageRow = document.createElement('div');
        newImageRow.id = `image_row_${imageIndex}`;
        newImageRow.className = 'flex items-center gap-2';
        newImageRow.innerHTML = `
            <input type="file" name="reference_images[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" multiple>
            <button type="button" onclick="removeReferenceImage(${imageIndex})" class="text-red-500 hover:text-red-700 font-bold p-1">&times;</button>
        `;
        container.appendChild(newImageRow);
        imageIndex++;
    }

    //fungsi untuk menghapus baris input file gambar, dengan validasi minimal 1 file
    function removeReferenceImage(index) {
        const row = document.getElementById(`image_row_${index}`);
        if (document.getElementById('reference_images_container').children.length > 1) {
            row.remove();
        } else {
            alert('Setidaknya satu file gambar wajib diunggah.');
        }
    }

    //fungsi untuk menghapus baris pajak lainnya
    function removeOtherTax(index) { document.getElementById(`tax_row_${index}`).remove(); calculateTotal(); }

    //fungsi untuk menghitung subtotal satu baris (jumlah x harga)
    function calculateSubtotal(index) {
        const qty = parseFloat(document.querySelector(`input[name="items[${index}][quantity]"]`).value) || 0;
        const price = parseFloat(document.querySelector(`input[name="items[${index}][price]"]`).value) || 0;
        document.getElementById(`subtotal_${index}`).textContent = formatCurrency(qty * price);
        calculateTotal();
    }

    // fungsi untuk menghitung semua total 
    function calculateTotal() {
        let subtotalItems = 0;
        //subtotal
        document.querySelectorAll('[id^="subtotal_"]').forEach(span => {
            subtotalItems += parseFloat(span.textContent.replace('Rp ', '').replace(/\./g, '').replace(',', '.')) || 0;
        });

        //diskon
        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        let discountAmount = discountType === 'percentage' ? (subtotalItems * discountValue) / 100 : discountValue;
        
        //pengenaan pajak
        const taxBase = subtotalItems - discountAmount;
        
        //PPN
        const ppnPercentage = parseFloat(document.getElementById('ppn_percentage').value) || 0;
        const ppnAmount = (taxBase * ppnPercentage) / 100;
        
        //total pajak
        let totalOtherTaxesAmount = 0;
        let otherTaxesSummaryHtml = '';
        document.querySelectorAll('[id^="tax_row_"]').forEach(row => {
            const nameInput = row.querySelector('input[type="text"]');
            const typeSelect = row.querySelector('select');
            const valueInput = row.querySelector('input[type="number"]');
            if (nameInput && typeSelect && valueInput && nameInput.value && (valueInput.value || valueInput.value === '0')) {
                const type = typeSelect.value;
                const value = parseFloat(valueInput.value) || 0;
                const taxAmount = type === 'percentage' ? (taxBase * value) / 100 : value;
                totalOtherTaxesAmount += taxAmount;
                const label = type === 'percentage' ? `${value}%` : formatCurrency(value);
                otherTaxesSummaryHtml += `<div class="flex justify-between"><span>${nameInput.value} (${label}):</span> <span>${formatCurrency(taxAmount)}</span></div>`;
            }
        });

        //total akhir
        const totalAmount = taxBase + ppnAmount + totalOtherTaxesAmount;

        //perbarui semua nilai di rincian
        document.getElementById('display_subtotal_items').textContent = formatCurrency(subtotalItems);
        document.getElementById('display_discount').textContent = `- ${formatCurrency(discountAmount)}`;
        document.getElementById('display_tax_base').textContent = formatCurrency(taxBase);
        document.getElementById('display_ppn_percentage_label').textContent = ppnPercentage;
        document.getElementById('display_ppn_amount').textContent = formatCurrency(ppnAmount);
        document.getElementById('display_other_taxes_summary').innerHTML = otherTaxesSummaryHtml;
        document.getElementById('display_total_amount').textContent = formatCurrency(totalAmount);
    }
</script>
@endpush
</x-app-layout>
