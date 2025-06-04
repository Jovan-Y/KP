<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Faktur - Pilih Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('invoices.create.step2') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="supplier_id" :value="__('Pilih Supplier')" />
                            <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <x-primary-button>
                                {{ __('Oke') }}
                            </x-primary-button>
                            <button type="button" onclick="openSupplierModal()" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-plus mr-2"></i> Tambah Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="supplierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Tambah Supplier Baru</h3>
            <form id="addSupplierForm">
                @csrf
                <div class="mb-4">
                    <x-input-label for="new_supplier_name" :value="__('Nama Supplier')" />
                    <x-text-input id="new_supplier_name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    <p class="text-red-500 text-xs mt-1" id="name_error"></p>
                </div>
                <div class="mb-4">
                    <x-input-label for="new_supplier_email" :value="__('Email Supplier')" />
                    <x-text-input id="new_supplier_email" class="block mt-1 w-full" type="email" name="email" required />
                    <p class="text-red-500 text-xs mt-1" id="email_error"></p> {{-- Tambahkan id untuk error handling --}}
                </div>
                <div class="mb-4">
                    <x-input-label for="new_supplier_phone" :value="__('Nomor Telepon')" />
                    <x-text-input id="new_supplier_phone" class="block mt-1 w-full" type="text" name="phone" />
                </div>
                <div class="flex justify-end space-x-2">
                    <x-secondary-button type="button" onclick="closeSupplierModal()">Batal</x-secondary-button>
                    <x-primary-button type="submit">Konfirmasi</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openSupplierModal() {
        console.log('openSupplierModal() dipanggil!'); // Tambahkan ini
        document.getElementById('supplierModal').classList.remove('hidden');
    }

        function closeSupplierModal() {
            document.getElementById('supplierModal').classList.add('hidden');
            document.getElementById('addSupplierForm').reset();
            document.getElementById('name_error').textContent = '';
        }

        document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
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
                    const selectElement = document.getElementById('supplier_id');
                    const newOption = document.createElement('option');
                    newOption.value = data.supplier.id;
                    newOption.textContent = data.supplier.name;
                    selectElement.appendChild(newOption);
                    selectElement.value = data.supplier.id; // Pilih supplier yang baru ditambahkan
                    closeSupplierModal();
                    alert('Supplier berhasil ditambahkan!');
                } else {
                    if (data.errors) {
                        document.getElementById('name_error').textContent = data.errors.name ? data.errors.name[0] : '';
                        document.getElementById('email_error').textContent = data.errors.email ? data.errors.email[0] : ''; // Tambahkan ini
                    } else {
                        alert('Gagal menambahkan supplier.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menambahkan supplier.');
            });
        });
    </script>
    @endpush
</x-app-layout>