<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Faktur #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Supplier: {{ $invoice->supplier->company_name }}</h3>
                    
                    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        @if ($errors->any())
                            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                                <strong class="font-bold">Oops! Ada beberapa kesalahan:</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div id="old-input-data"
                             data-old-items="{{ json_encode(old('items', $invoice->invoiceItems->toArray())) }}"
                             data-old-taxes="{{ json_encode(old('other_taxes', $invoice->other_taxes)) }}">
                        </div>
                        <input type="hidden" name="images_to_delete" id="images_to_delete_input">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="invoice_number" value="Nomor Faktur" />
                                <x-text-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number', $invoice->invoice_number)" required />
                            </div>
                            <div>
                                <x-input-label for="po_number" value="Nomor PO (Opsional)" />
                                <x-text-input id="po_number" class="block mt-1 w-full" type="text" name="po_number" :value="old('po_number', $invoice->po_number)" />
                            </div>
                            <div>
                                <x-input-label for="invoice_date" value="Tanggal Faktur" />
                                <x-text-input id="invoice_date" class="block mt-1 w-full" type="date" name="invoice_date" :value="old('invoice_date', $invoice->invoice_date->format('Y-m-d'))" required />
                            </div>
                             <div>
                                <x-input-label for="received_date" value="Tanggal Diterima" />
                                <x-text-input id="received_date" class="block mt-1 w-full" type="date" name="received_date" :value="old('received_date', $invoice->received_date->format('Y-m-d'))" required />
                            </div>
                             <div id="due_date_wrapper">
                                <x-input-label for="due_date" value="Jatuh Tempo" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '')" />
                            </div>
                             <div class="md:col-span-3">
                                <x-input-label value="Detail Pembayaran" />
                                <div class="flex items-center space-x-4 mt-1">
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_type" value="debit" class="payment-type-radio text-indigo-600 focus:ring-indigo-500" {{ old('payment_type', $invoice->payment_type) == 'debit' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Debit</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="payment_type" value="kredit" class="payment-type-radio text-indigo-600 focus:ring-indigo-500" {{ old('payment_type', $invoice->payment_type) == 'kredit' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Kredit</span>
                                    </label>
                                </div>
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
                                        <select name="discount_type" id="discount_type" class="block border-gray-300 rounded-md shadow-sm" onchange="calculateTotal()">
                                            <option value="fixed" {{ old('discount_type', $invoice->discount_type) == 'fixed' ? 'selected' : '' }}>Rp</option>
                                            <option value="percentage" {{ old('discount_type', $invoice->discount_type) == 'percentage' ? 'selected' : '' }}>%</option>
                                        </select>
                                        <x-text-input id="discount_value" class="block w-full" type="number" name="discount_value" :value="old('discount_value', $invoice->discount_value)" min="0" oninput="calculateTotal()" />
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="ppn_percentage" value="PPN (%)" />
                                    <x-text-input id="ppn_percentage" class="block mt-1 w-full" type="number" name="ppn_percentage" :value="old('ppn_percentage', $invoice->ppn_percentage)" step="0.01" min="0" max="100" oninput="calculateTotal()" />
                                </div>
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
                                                <div onclick="unmarkImageForDeletion(this)" data-image-id="{{ $image->id }}" class="absolute inset-0 bg-red-600 bg-opacity-70 flex-col items-center justify-center text-white hidden cursor-pointer group" title="Batal Hapus">
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


                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                Simpan Perubahan
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
    let imageEditIndex = 0;

    document.addEventListener('DOMContentLoaded', () => {
        const dataElement = document.getElementById('old-input-data');
        if (!dataElement) return;

        const oldItems = JSON.parse(dataElement.dataset.oldItems || '[]');
        const oldTaxes = JSON.parse(dataElement.dataset.oldTaxes || '[]');

        if (oldItems && oldItems.length > 0) {
            oldItems.forEach(itemData => addItem(itemData));
        } else {
            addItem(); 
        }

        if (oldTaxes && oldTaxes.length > 0) {
            oldTaxes.forEach(taxData => addOtherTax(taxData));
        }

        // Untuk halaman edit
        if (document.getElementById('reference_images_container_edit')) {
            addReferenceImageEdit(); 
        }
        
        calculateTotal(); 

        const paymentTypeRadios = document.querySelectorAll('.payment-type-radio');
        const dueDateWrapper = document.getElementById('due_date_wrapper');
        const dueDateInput = document.getElementById('due_date');

        function toggleDueDate() {
            if (!dueDateWrapper || !dueDateInput) return;
            const selectedRadio = document.querySelector('.payment-type-radio:checked');
            if (selectedRadio && selectedRadio.value === 'debit') {
                dueDateWrapper.style.display = 'none';
                dueDateInput.removeAttribute('required');
            } else {
                dueDateWrapper.style.display = 'block';
                dueDateInput.setAttribute('required', 'required');
            }
        }
        paymentTypeRadios.forEach(radio => radio.addEventListener('change', toggleDueDate));
        toggleDueDate();
    });

    function formatCurrency(value) { return `Rp ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)}`; }

    function addItem(data = {}) {
        const tableBody = document.querySelector('#items_table tbody');
        if (!tableBody) return;
        
        const newRow = document.createElement('tr');
        newRow.id = `item_row_${itemIndex}`;
        newRow.innerHTML = `
            <td class="px-1 py-2"><input type="text" name="items[${itemIndex}][item_name]" value="${data.item_name || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required /></td>
            <td class="px-1 py-2" style="width: 100px;"><input type="number" name="items[${itemIndex}][quantity]" value="${data.quantity || 1}" min="1" oninput="calculateSubtotal(${itemIndex})" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required /></td>
            <td class="px-1 py-2" style="width: 120px;"><input type="text" name="items[${itemIndex}][unit]" value="${data.unit || 'Pcs'}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required /></td>
            <td class="px-1 py-2" style="width: 150px;"><input type="number" name="items[${itemIndex}][price]" value="${data.price || 0}" step="0.01" min="0" oninput="calculateSubtotal(${itemIndex})" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required /></td>
            <td class="px-1 py-2 text-sm whitespace-nowrap"><span id="subtotal_${itemIndex}">Rp 0,00</span></td>
            <td class="px-1 py-2 text-center"><button type="button" onclick="removeItem(${itemIndex})" class="text-red-500 hover:text-red-700">&times;</button></td>
        `;
        tableBody.appendChild(newRow);
        calculateSubtotal(itemIndex);
        itemIndex++;
    }

    function removeItem(index) { document.getElementById(`item_row_${index}`).remove(); calculateTotal(); }

    function addOtherTax(data = {}) {
        const container = document.getElementById('other_taxes_container');
        if (!container) return;
        
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

    function removeOtherTax(index) { document.getElementById(`tax_row_${index}`).remove(); calculateTotal(); }

    function calculateSubtotal(index) {
        const qtyEl = document.querySelector(`input[name="items[${index}][quantity]"]`);
        const priceEl = document.querySelector(`input[name="items[${index}][price]"]`);
        const subtotalEl = document.getElementById(`subtotal_${index}`);
        if (!qtyEl || !priceEl || !subtotalEl) return;
        
        const qty = parseFloat(qtyEl.value) || 0;
        const price = parseFloat(priceEl.value) || 0;
        subtotalEl.textContent = formatCurrency(qty * price);
        calculateTotal();
    }

    function calculateTotal() {
        let subtotalItems = 0;
        document.querySelectorAll('[id^="subtotal_"]').forEach(span => {
            subtotalItems += parseFloat(span.textContent.replace('Rp ', '').replace(/\./g, '').replace(',', '.')) || 0;
        });

        const discountType = document.getElementById('discount_type')?.value;
        const discountValue = parseFloat(document.getElementById('discount_value')?.value) || 0;
        let discountAmount = discountType === 'percentage' ? (subtotalItems * discountValue) / 100 : discountValue;
        
        const taxBase = subtotalItems - discountAmount;

        const ppnPercentage = parseFloat(document.getElementById('ppn_percentage')?.value) || 0;
        const ppnAmount = (taxBase * ppnPercentage) / 100;
        
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

        const totalAmount = taxBase + ppnAmount + totalOtherTaxesAmount;

        document.getElementById('display_subtotal_items').textContent = formatCurrency(subtotalItems);
        document.getElementById('display_discount').textContent = `- ${formatCurrency(discountAmount)}`;
        document.getElementById('display_tax_base').textContent = formatCurrency(taxBase);
        document.getElementById('display_ppn_percentage_label').textContent = ppnPercentage;
        document.getElementById('display_ppn_amount').textContent = formatCurrency(ppnAmount);
        document.getElementById('display_other_taxes_summary').innerHTML = otherTaxesSummaryHtml;
        document.getElementById('display_total_amount').textContent = formatCurrency(totalAmount);
    }

    function addReferenceImageEdit() {
        const container = document.getElementById('reference_images_container_edit');
        if(!container) return;
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
        document.getElementById(`image_edit_row_${index}`).remove();
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
