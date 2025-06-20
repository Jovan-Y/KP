<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cari Faktur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('search.results') }}" method="GET">
                        
                        {{-- Menampilkan error validasi dari server --}}
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="invoice_number" value="Nomor Faktur" />
                                <x-text-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="request('invoice_number')" />
                            </div>
                            
                            {{-- ================= AWAL KODE PERUBAHAN ================= --}}
                            <div>
                                <x-input-label for="supplier_id" value="Supplier" />
                                <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Semua Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- ================= AKHIR KODE PERUBAHAN ================= --}}
                        </div>

                        <div class="mt-4">
                            <label class="font-medium text-sm text-gray-700">Rentang Tanggal Jatuh Tempo</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-1">
                                <div>
                                    <x-input-label for="due_date_from" value="Dari Tanggal" />
                                    <x-text-input id="due_date_from" class="block mt-1 w-full" type="date" name="due_date_from" :value="request('due_date_from')" oninput="validateDateRange()" />
                                </div>
                                <div>
                                    <x-input-label for="due_date_to" value="Sampai Tanggal" />
                                    <x-text-input id="due_date_to" class="block mt-1 w-full" type="date" name="due_date_to" :value="request('due_date_to')" oninput="validateDateRange()" />
                                </div>
                            </div>
                            <p id="date-range-warning" class="text-red-500 text-sm mt-2 hidden">
                                Tanggal "Sampai" tidak boleh lebih awal dari tanggal "Dari".
                            </p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="status" value="Status Pembayaran" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button id="search-button">
                                Cari Faktur
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function validateDateRange() {
            const dateFromInput = document.getElementById('due_date_from');
            const dateToInput = document.getElementById('due_date_to');
            const warningMessage = document.getElementById('date-range-warning');
            const searchButton = document.getElementById('search-button');

            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;

            if (dateFrom && dateTo) {
                if (dateTo < dateFrom) {
                    warningMessage.classList.remove('hidden');
                    searchButton.disabled = true;
                    searchButton.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    warningMessage.classList.add('hidden');
                    searchButton.disabled = false;
                    searchButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } else {
                 warningMessage.classList.add('hidden');
                 searchButton.disabled = false;
                 searchButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        document.addEventListener('DOMContentLoaded', validateDateRange);
    </script>
    @endpush
</x-app-layout>
