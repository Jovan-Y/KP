<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Halaman Utama') }}
        </h2>
    </x-slot>

    <div class="p-6 text-gray-900">
    <div class="p-6 text-gray-900">
    <h3 class="text-lg font-medium text-gray-900">Pemberitahuan</h3>
    <p class="mt-1 text-sm text-gray-600">
        Ada **{{ $unpaidInvoicesCount }}** faktur yang belum lunas.
    </p>
    <div class="mt-4">
        <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            Lihat Daftar Faktur
        </a>
    </div>


    @if($newSupplierUploadsCount > 0)
        <div class="mt-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">Unggahan Baru!</p>
            <p>Ada **{{ $newSupplierUploadsCount }}** gambar baru dari supplier yang perlu direview.
               <a href="{{ route('manager.supplier_uploads.index') }}" class="underline font-semibold">Lihat sekarang.</a>
            </p>
        </div>
    @endif

    </div>
</div>
</x-app-layout>