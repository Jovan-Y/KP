<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="page-notification" class="mb-4 hidden"></div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Daftar Semua Supplier</h3>
                        <button type="button" onclick="openSupplierModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Supplier Baru
                        </button>
                    </div>

                    <div class="overflow-auto relative max-h-[80vh]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Info Pembayaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $supplier->company_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->address }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if(!empty($supplier->payment_details) && is_array($supplier->payment_details))
                                                @foreach($supplier->payment_details as $detail)
                                                    <div class="mb-1 last:mb-0">
                                                        <p class="text-xs font-semibold">{{ strtoupper($detail['bank_name'] ?? '') }}</p>
                                                        <p class="text-xs">a/n: {{ $detail['account_name'] ?? '' }}</p>
                                                        <p class="text-xs">({{ $detail['account_number'] ?? '' }})</p>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-xs italic">Tidak ada data</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{-- 
                                                PERBAIKAN FINAL:
                                                Menggunakan @json untuk mengubah objek PHP menjadi string JSON yang aman.
                                                String ini dimasukkan ke dalam atribut `data-supplier`, yang merupakan cara standar
                                                dan aman untuk menyimpan data pada elemen HTML. `onclick` hanya memanggil fungsi
                                                dengan `this`, yang merujuk ke elemen tombol itu sendiri.
                                            --}}
                                            <button 
                                                data-supplier='{{ json_encode($supplier) }}' 
                                                onclick='openEditModal(this)' 
                                                class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Belum ada data supplier.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah & Edit --}}
    {{-- (Tidak ada perubahan pada HTML modal, hanya pada JavaScript di bawah) --}}

    {{-- Modal Tambah Supplier --}}
    <div id="supplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Tambah Supplier Baru</h3>
            <div id="modal_error_notification" class="hidden mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded-md text-sm"></div>
            <form id="addSupplierForm" onsubmit="handleSupplierSubmit(event)" novalidate>
                <div class="space-y-4">
                    <div>
                        <x-input-label for="new_company_name" value="Nama Perusahaan" />
                        <x-text-input id="new_company_name" class="block mt-1 w-full" type="text" name="company_name" />
                        <p class="text-red-500 text-xs mt-1" id="company_name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_name" value="Nama Kontak (PIC)" />
                        <x-text-input id="new_name" class="block mt-1 w-full" type="text" name="name" />
                        <p class="text-red-500 text-xs mt-1" id="name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_email" value="Email Supplier (opsional)" />
                        <x-text-input id="new_email" class="block mt-1 w-full" type="email" name="email" />
                        <p class="text-red-500 text-xs mt-1" id="email_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_phone" value="Nomor Telepon" />
                        <x-text-input id="new_phone" class="block mt-1 w-full" type="text" name="phone" />
                        <p class="text-red-500 text-xs mt-1" id="phone_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_address" value="Alamat Supplier (opsional)" />
                        <textarea id="new_address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                        <p class="text-red-500 text-xs mt-1" id="address_error"></p>
                    </div>
                    <div class="mt-4 border-t pt-4">
                        <label class="font-medium text-sm text-gray-700">Detail Pembayaran</label>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-start gap-2">
                                <div class="flex-1">
                                    <input type="text" name="payment_details[0][bank_name]" placeholder="Nama Bank" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <p class="text-red-500 text-xs mt-1" id="payment_details.0.bank_name_error"></p>
                                </div>
                                <div class="flex-1">
                                    <input type="text" name="payment_details[0][account_number]" placeholder="Nomor Rekening" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <p class="text-red-500 text-xs mt-1" id="payment_details.0.account_number_error"></p>
                                </div>
                                <div class="flex-1">
                                    <input type="text" name="payment_details[0][account_name]" placeholder="Atas Nama" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <p class="text-red-500 text-xs mt-1" id="payment_details.0.account_name_error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <x-secondary-button type="button" onclick="closeSupplierModal()">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Supplier</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Supplier --}}
    <div id="editSupplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Edit Informasi Supplier</h3>
            <div id="edit_modal_error_notification" class="hidden mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded-md text-sm"></div>
            <form id="editSupplierForm" onsubmit="handleSupplierUpdate(event)" novalidate>
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit_supplier_id" name="id">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="edit_company_name" value="Nama Perusahaan" />
                        <x-text-input id="edit_company_name" class="block mt-1 w-full" type="text" name="company_name" />
                        <p class="text-red-500 text-xs mt-1" id="edit_company_name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="edit_name" value="Nama Kontak (PIC)" />
                        <x-text-input id="edit_name" class="block mt-1 w-full" type="text" name="name" />
                        <p class="text-red-500 text-xs mt-1" id="edit_name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="edit_email" value="Email Supplier (opsional)" />
                        <x-text-input id="edit_email" class="block mt-1 w-full" type="email" name="email" />
                        <p class="text-red-500 text-xs mt-1" id="edit_email_error"></p>
                    </div>
                    <div>
                        <x-input-label for="edit_phone" value="Nomor Telepon" />
                        <x-text-input id="edit_phone" class="block mt-1 w-full" type="text" name="phone" />
                        <p class="text-red-500 text-xs mt-1" id="edit_phone_error"></p>
                    </div>
                    <div>
                        <x-input-label for="edit_address" value="Alamat Supplier (opsional)" />
                        <textarea id="edit_address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                        <p class="text-red-500 text-xs mt-1" id="edit_address_error"></p>
                    </div>
                    <div class="mt-4 border-t pt-4">
                        <label class="font-medium text-sm text-gray-700">Detail Pembayaran</label>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-start gap-2">
                                <div class="flex-1">
                                    <input type="text" id="edit_bank_name" name="payment_details[0][bank_name]" placeholder="Nama Bank" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                </div>
                                <div class="flex-1">
                                    <input type="text" id="edit_account_number" name="payment_details[0][account_number]" placeholder="Nomor Rekening" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                </div>
                                <div class="flex-1">
                                    <input type="text" id="edit_account_name" name="payment_details[0][account_name]" placeholder="Atas Nama" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <x-secondary-button type="button" onclick="closeEditModal()">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>


    @push('scripts')
    <script>
        const errorNotification = document.getElementById('modal_error_notification');
        const pageNotification = document.getElementById('page-notification');
        const editErrorNotification = document.getElementById('edit_modal_error_notification');
        
        function openSupplierModal() { document.getElementById('supplierModal').classList.remove('hidden'); }
        function closeSupplierModal() {
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('addSupplierForm').reset();
            resetErrors();
        }

        // PERBAIKAN: Fungsi sekarang menerima elemen tombol sebagai argumen
        function openEditModal(button) {
            // Ambil data dari atribut 'data-supplier' dan parse sebagai JSON
            const supplier = JSON.parse(button.getAttribute('data-supplier'));

            resetErrors('edit_');
            
            document.getElementById('edit_supplier_id').value = supplier.id;
            document.getElementById('edit_company_name').value = supplier.company_name;
            document.getElementById('edit_name').value = supplier.name;
            document.getElementById('edit_email').value = supplier.email || '';
            document.getElementById('edit_phone').value = supplier.phone;
            document.getElementById('edit_address').value = supplier.address || '';

            if (supplier.payment_details && supplier.payment_details.length > 0) {
                const detail = supplier.payment_details[0];
                document.getElementById('edit_bank_name').value = detail.bank_name || '';
                document.getElementById('edit_account_name').value = detail.account_name || '';
                document.getElementById('edit_account_number').value = detail.account_number || '';
            }

            document.getElementById('editSupplierModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editSupplierModal').classList.add('hidden');
            document.getElementById('editSupplierForm').reset();
            resetErrors('edit_');
        }
        
        function resetErrors(prefix = '') {
            const notification = prefix ? editErrorNotification : errorNotification;
            if(notification) notification.classList.add('hidden');
            document.querySelectorAll(`p[id^="${prefix}"][id$="_error"]`).forEach(el => el.textContent = '');
        }

        function showPageNotification(message, isSuccess = true) {
            pageNotification.textContent = message;
            pageNotification.className = `mb-4 p-4 rounded-md ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            pageNotification.classList.remove('hidden');
            setTimeout(() => {
                pageNotification.classList.add('hidden');
            }, 3000);
        }
        
        function handleSupplierSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            resetErrors();

            fetch('{{ route('suppliers.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => Promise.reject(err));
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeSupplierModal();
                    showPageNotification(data.message, true);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(errorData => {
               if (errorData && errorData.errors) {
                    if(errorNotification) {
                        errorNotification.textContent = 'Gagal menyimpan. Silakan periksa kembali isian Anda.';
                        errorNotification.classList.remove('hidden');
                    }
                    
                    for (const key in errorData.errors) {
                        const errorElement = document.getElementById(key.replace(/\./g, '\\.') + '_error');
                        if (errorElement) {
                            errorElement.textContent = errorData.errors[key][0];
                        }
                    }
               } else {
                    console.error('Error:', errorData);
                    if(errorNotification) {
                        errorNotification.textContent = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                        errorNotification.classList.remove('hidden');
                    }
               }
            });
        }

        function handleSupplierUpdate(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const supplierId = document.getElementById('edit_supplier_id').value;
            const url = `/suppliers/${supplierId}`;

            resetErrors('edit_');

            // PERBAIKAN: Tidak ada komentar di dalam objek `fetch`
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => Promise.reject(err));
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    showPageNotification(data.message, true);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(errorData => {
                if (errorData && errorData.errors) {
                    if(editErrorNotification) {
                        editErrorNotification.textContent = 'Gagal menyimpan perubahan. Silakan periksa kembali isian Anda.';
                        editErrorNotification.classList.remove('hidden');
                    }

                    for (const key in errorData.errors) {
                        const errorElement = document.getElementById('edit_' + key.replace(/\./g, '\\.') + '_error');
                        if (errorElement) {
                            errorElement.textContent = errorData.errors[key][0];
                        }
                    }
                } else {
                    console.error('Error:', errorData);
                    if(editErrorNotification) {
                        editErrorNotification.textContent = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.';
                        editErrorNotification.classList.remove('hidden');
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>