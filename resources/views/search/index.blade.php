<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cari Faktur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('search.results') }}" method="GET">
                        <div class="mb-4">
                            <x-input-label for="invoice_number" :value="__('Nomor Faktur')" />
                            <x-text-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="supplier_id" :value="__('Supplier')" />
                            <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Semua Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="due_date_proximity" :value="__('Tanggal Jatuh Tempo')" />
                            <select id="due_date_proximity" name="due_date_proximity" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Semua Tanggal --</option>
                                <option value="next_7_days" {{ old('due_date_proximity') == 'next_7_days' ? 'selected' : '' }}>7 Hari ke Depan</option>
                                </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="is_paid" :value="__('Status Pelunasan')" />
                            <select id="is_paid" name="is_paid" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="0" {{ old('is_paid') === '0' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="1" {{ old('is_paid') === '1' ? 'selected' : '' }}>Sudah Lunas</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Cari') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>