<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Halaman Utama') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">Pemberitahuan</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Ada **{{ $unpaidInvoicesCount }}** faktur yang belum lunas.
                    </p>

                    @if($dueDateSoonInvoices->count() > 0)
                        <div class="mt-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                            <p class="font-bold">Perhatian!</p>
                            <p>Faktur berikut akan segera jatuh tempo:</p>
                            <ul class="list-disc ml-5">
                                @foreach($dueDateSoonInvoices as $invoice)
                                    <li><a href="{{ route('invoices.show', $invoice->id) }}" class="underline">{{ $invoice->invoice_number }}</a> (Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Lihat Faktur
                        </a>
                        @if(Auth::user()->role === 'manager')
                            <a href="{{ route('invoices.create.step1') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Tambah Faktur
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>