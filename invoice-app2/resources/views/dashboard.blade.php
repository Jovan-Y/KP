<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Halaman Utama
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Blok Notifikasi Faktur Terlewat Jatuh Tempo --}}
                    @if($overdueInvoices->isNotEmpty())
                        <div class="p-4 bg-red-200 border-l-4 border-red-600 text-red-900 rounded-md">
                            <h3 class="font-bold text-lg mb-2">Peringatan Keras: {{ $overdueInvoices->count() }} Faktur Terlewat Jatuh Tempo!</h3>
                            <div class="space-y-3 max-h-56 overflow-y-auto pr-2">
                                @foreach($overdueInvoices as $invoice)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                        $overdueDays = $dueDate->diffInDays(\Carbon\Carbon::today());
                                        $overdueText = "Terlewat <span class='font-bold'>{$overdueDays} hari</span>";
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-red-100 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-red-900">No: {{ $invoice->invoice_number }}</p>
                                            <p class="text-sm">Supplier: <strong>{{ $invoice->supplier->company_name }}</strong></p>
                                            <div class="text-sm mt-1">
                                                <span>{!! $overdueText !!}</span>
                                                <span class="mx-1">&bull;</span>
                                                <span>Pembayaran: <span class="font-semibold">{{ ucfirst($invoice->payment_type) }}</span></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="inline-block px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-md hover:bg-red-700 transition flex-shrink-0">
                                            Lihat Detail
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Blok Notifikasi Faktur Jatuh Tempo Mendesak --}}
                    @if($urgentInvoices->isNotEmpty())
                        <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-md">
                            <h3 class="font-bold text-lg mb-2">Peringatan: {{ $urgentInvoices->count() }} Faktur Jatuh Tempo Mendesak</h3>
                            <div class="space-y-3 max-h-56 overflow-y-auto pr-2">
                                @foreach($urgentInvoices as $invoice)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                        $dueText = '';
                                        if ($dueDate->isToday()) {
                                            $dueText = 'Jatuh tempo <span class="font-bold">hari ini</span>';
                                        } elseif ($dueDate->isTomorrow()) {
                                            $dueText = 'Jatuh tempo <span class="font-bold">besok</span>';
                                        }
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-yellow-900">No: {{ $invoice->invoice_number }}</p>
                                            <p class="text-sm text-yellow-700">Supplier: <strong>{{ $invoice->supplier->company_name }}</strong></p>
                                            <div class="text-sm text-yellow-700 mt-1">
                                                <span>{!! $dueText !!}</span>
                                                <span class="mx-1">&bull;</span>
                                                <span>Pembayaran: <span class="font-semibold">{{ ucfirst($invoice->payment_type) }}</span></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="inline-block px-3 py-1 bg-yellow-500 text-white text-xs font-bold rounded-md hover:bg-yellow-600 transition flex-shrink-0">
                                            Lihat Detail
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Pemberitahuan Peringatan 3 Hari Mendatang --}}
                    @if($upcomingInvoices->isNotEmpty())
                        <div class="p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-800 rounded-md">
                             <h3 class="font-bold text-lg mb-2">Perhatian: {{ $upcomingInvoices->count() }} Faktur Jatuh Tempo dalam 3 Hari</h3>
                             <div class="space-y-3 max-h-56 overflow-y-auto pr-2">
                                @foreach($upcomingInvoices as $invoice)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                        $diffInDays = $dueDate->diffInDays(\Carbon\Carbon::today());
                                        $dueText = "Jatuh tempo dalam <span class='font-bold'>{$diffInDays} hari</span>";
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-blue-900">No: {{ $invoice->invoice_number }}</p>
                                            <p class="text-sm text-blue-700">Supplier: <strong>{{ $invoice->supplier->company_name }}</strong></p>
                                            <div class="text-sm text-blue-700 mt-1">
                                                <span>{!! $dueText !!}</span>
                                                <span class="mx-1">&bull;</span>
                                                <span>Pembayaran: <span class="font-semibold">{{ ucfirst($invoice->payment_type) }}</span></span>
                                            </div>
                                        </div>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-md hover:bg-blue-700 transition flex-shrink-0">
                                            Lihat Detail
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- Pemberitahuan Umum --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Pemberitahuan Umum</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Saat ini ada <strong>{{ $unpaidInvoicesCount }}</strong> faktur yang belum lunas secara keseluruhan.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Lihat Semua Faktur
                            </a>
                        </div>
                    </div>
                
                        <div class="border-t pt-6">
                            <div class="mt-4 flex flex-wrap gap-4">
                                <a href="{{ route('invoices.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Tambah Faktur Baru
                                </a>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
