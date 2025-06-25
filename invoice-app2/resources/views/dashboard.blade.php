<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('invoices.create.step1') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    {{ __('Tambah Faktur Baru') }}
                </a>
                 <a href="{{ route('invoices.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Lihat Semua Faktur') }}
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Faktur Belum Lunas</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 ">{{ $unpaidInvoicesCount }}</p>
                    </div>
                    @if($unpaidInvoicesCount > 0)
                        <div class="mt-4">
                            <a href="{{ route('invoices.index') }}" class="w-full text-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Lihat Semua
                            </a>
                        </div>
                    @endif
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-red-500 truncate">Terlewat Jatuh Tempo</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-600">{{ $overdueInvoicesCount }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-orange-500">Akan Jatuh Tempo (3 Hari)</h3>
                    <p class="mt-1 text-3xl font-semibold text-orange-600">{{ $upcomingInvoicesCount }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                    <div class="p-6 text-gray-900 ">
                        <h3 class="font-semibold text-lg text-red-600 mb-4">Terlewat Jatuh Tempo ({{ $overdueInvoices->count() }})</h3>
                        <div class="max-h-80 overflow-y-auto pr-2">
                            @forelse($overdueInvoices as $invoice)
                                <div class="py-2 border-b border-gray-200 last:border-b-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold">{{ $invoice->invoice_number }}</p>
                                            <p class="text-sm text-gray-600">{{ $invoice->supplier->company_name }} - 
                                                <span class="font-semibold">Lewat {{ now()->startOfDay()->diffInDays($invoice->due_date) }} hari</span>
                                            </p>
                                        </div>
                                        <div class="text-right flex-shrink-0 ml-4">
                                            @if(!empty($invoice->supplier->payment_details) && is_array($invoice->supplier->payment_details))
                                                @foreach($invoice->supplier->payment_details as $detail)
                                                    <p class="text-xs text-gray-600 ">a/n: {{ $detail['account_name'] ?? '' }}</p>
                                                    <p class="text-xs text-gray-600">{{ strtoupper($detail['bank_name'] ?? '') }} ({{ $detail['account_number'] ?? '' }})</p>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 ">Tidak ada faktur yang terlewat jatuh tempo.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="p-6 text-gray-900 ">
                        <h3 class="font-semibold text-lg text-blue-600 mb-4">Akan Jatuh Tempo (3 Hari)</h3>
                        <div class="max-h-80 overflow-y-auto pr-2">
                            @forelse($upcomingInvoices as $invoice)
                            <div class="py-2 border-b border-gray-200 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold">{{ $invoice->invoice_number }}</p>
                                        {{-- PERUBAHAN: Logika Tampilan Hari Lebih Andal --}}
                                        <p class="text-sm text-orange-500 font-semibold">{{ $invoice->supplier->company_name }} - 
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                            @endphp

                                            @if($dueDate->isToday())
                                                Jatuh tempo Hari Ini
                                            @elseif($dueDate->isTomorrow())
                                                Jatuh tempo Besok
                                            @else
                                                Jatuh tempo dalam {{ $dueDate->diffInDays(now()) }} hari
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-4">
                                        @if(!empty($invoice->supplier->payment_details) && is_array($invoice->supplier->payment_details))
                                            @foreach($invoice->supplier->payment_details as $detail)
                                                <p class="text-xs text-gray-600">a/n: {{ $detail['account_name'] ?? '' }}</p>
                                                <p class="text-xs text-gray-600">{{ strtoupper($detail['bank_name'] ?? '') }} ({{ $detail['account_number'] ?? '' }})</p>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                                <p class="text-gray-500">Tidak ada faktur yang akan jatuh tempo dalam 3 hari ke depan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>