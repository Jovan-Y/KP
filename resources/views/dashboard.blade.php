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