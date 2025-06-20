<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Hasil Pencarian Faktur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('search.index') }}" class="text-blue-500 hover:text-blue-700">&larr; Kembali ke Pencarian</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg border">
                        <h4 class="font-bold text-gray-700">Kriteria Pencarian:</h4>
                        <ul class="list-disc list-inside text-sm text-gray-600 mt-2">
                            @if(!empty($searchCriteria['invoice_number']))
                                <li>Nomor Faktur mengandung: <strong>{{ $searchCriteria['invoice_number'] }}</strong></li>
                            @endif
                            
                            {{-- Menampilkan nama supplier yang dipilih --}}
                            @if(!empty($searchCriteria['supplier_id']))
                                @php
                                    $selectedSupplier = $suppliers->firstWhere('id', $searchCriteria['supplier_id']);
                                @endphp
                                <li>Supplier: <strong>{{ $selectedSupplier ? $selectedSupplier->company_name : 'Tidak Ditemukan' }}</strong></li>
                            @endif

                            @if(!empty($searchCriteria['due_date_from']))
                                <li>Jatuh Tempo Dari: <strong>{{ \Carbon\Carbon::parse($searchCriteria['due_date_from'])->format('d M Y') }}</strong></li>
                            @endif
                            @if(!empty($searchCriteria['due_date_to']))
                                <li>Jatuh Tempo Sampai: <strong>{{ \Carbon\Carbon::parse($searchCriteria['due_date_to'])->format('d M Y') }}</strong></li>
                            @endif
                            @if(!empty($searchCriteria['status']))
                                <li>Status: <strong>{{ $searchCriteria['status'] == 'paid' ? 'Lunas' : 'Belum Lunas' }}</strong></li>
                            @endif
                        </ul>
                    </div>
                    
                    <h3 class="text-xl font-bold mb-4">Ditemukan {{ $invoices->total() }} Faktur</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor Faktur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->supplier->company_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $invoice->is_paid ? 'Lunas' : 'Belum Lunas' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-500">Tidak ada faktur yang cocok dengan kriteria pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
