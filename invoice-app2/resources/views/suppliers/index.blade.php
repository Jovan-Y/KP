<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notifikasi --}}
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
                        {{-- Tombol untuk membuka modal tambah supplier --}}
                        <button type="button" onclick="openSupplierModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Tambah Supplier Baru
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Perusahaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak Person</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email & Telepon</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $supplier->company_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->address }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $supplier->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $supplier->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $supplier->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus supplier ini? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit">Hapus</x-danger-button>
                                            </form>
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

    {{-- JENDELA POP-UP (MODAL) UNTUK TAMBAH SUPPLIER --}}
    <div id="supplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Tambah Supplier Baru</h3>
            <form id="addSupplierForm" onsubmit="handleSupplierSubmit(event)">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="new_company_name" value="Nama Perusahaan" />
                        <x-text-input id="new_company_name" class="block mt-1 w-full" type="text" name="company_name" required />
                        <p class="text-red-500 text-xs mt-1" id="company_name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_supplier_name" value="Nama Kontak (PIC)" />
                        <x-text-input id="new_supplier_name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                        <p class="text-red-500 text-xs mt-1" id="name_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_supplier_email" value="Email Supplier" />
                        <x-text-input id="new_supplier_email" class="block mt-1 w-full" type="email" name="email" required />
                        <p class="text-red-500 text-xs mt-1" id="email_error"></p>
                    </div>
                    <div>
                        <x-input-label for="new_supplier_phone" value="Nomor Telepon" />
                        <x-text-input id="new_supplier_phone" class="block mt-1 w-full" type="text" name="phone" />
                    </div>
                    <div>
                        <x-input-label for="new_supplier_address" value="Alamat Supplier" />
                        <textarea id="new_supplier_address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                    </div>
                    <div class="mt-4 border-t pt-4">
                        <label class="font-medium text-sm text-gray-700">Detail Pembayaran</label>
                        <div id="payment_details_container" class="mt-2 space-y-2">
                            {{-- Detail pembayaran akan ditambahkan oleh JavaScript --}}
                        </div>
                        <button type="button" onclick="addPaymentDetail()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Tambah Detail Pembayaran</button>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <x-secondary-button type="button" onclick="closeSupplierModal()">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Supplier</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let paymentDetailIndex = 0;
        
        function openSupplierModal() { document.getElementById('supplierModal').classList.remove('hidden'); }
        function closeSupplierModal() {
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('addSupplierForm').reset();
            document.getElementById('name_error').textContent = '';
            document.getElementById('company_name_error').textContent = '';
            document.getElementById('email_error').textContent = '';
        }

        function addPaymentDetail() {
            const container = document.getElementById('payment_details_container');
            const newRow = document.createElement('div');
            newRow.id = `payment_row_${paymentDetailIndex}`;
            newRow.className = 'flex items-center gap-2';
            newRow.innerHTML = `
                <input type="text" name="payment_details[${paymentDetailIndex}][bank_name]" placeholder="Nama Bank (e.g. BCA)" class="mt-1 block w-1/3 border-gray-300 rounded-md shadow-sm text-sm">
                <input type="text" name="payment_details[${paymentDetailIndex}][account_number]" placeholder="Nomor Rekening" class="mt-1 block w-1/3 border-gray-300 rounded-md shadow-sm text-sm">
                <input type="text" name="payment_details[${paymentDetailIndex}][account_name]" placeholder="Atas Nama" class="mt-1 block w-1/3 border-gray-300 rounded-md shadow-sm text-sm">
                <button type="button" onclick="removePaymentDetail(${paymentDetailIndex})" class="text-red-500 hover:text-red-700">&times;</button>
            `;
            container.appendChild(newRow);
            paymentDetailIndex++;
        }

        function removePaymentDetail(index) {
            document.getElementById(`payment_row_${index}`).remove();
        }

        function handleSupplierSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('{{ route('suppliers.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSupplierModal();
                    alert('Supplier berhasil ditambahkan!');
                    location.reload(); // Muat ulang halaman untuk menampilkan data baru
                } else {
                    if (data.errors) {
                        document.getElementById('name_error').textContent = data.errors.name ? data.errors.name[0] : '';
                        document.getElementById('company_name_error').textContent = data.errors.company_name ? data.errors.company_name[0] : '';
                        document.getElementById('email_error').textContent = data.errors.email ? data.errors.email[0] : '';
                    } else {
                        alert('Gagal menambahkan supplier.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
        }
    </script>
    @endpush
</x-app-layout>
