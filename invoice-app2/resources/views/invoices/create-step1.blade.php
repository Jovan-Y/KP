<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Faktur - Pilih Supplier
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('invoices.create.post_step1') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="supplier_id" value="Pilih Supplier yang Sudah Ada" />
                            <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->company_name }} ({{ $supplier->name }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-start mt-6">
                            <x-primary-button>
                                Lanjutkan ke Detail Faktur
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
