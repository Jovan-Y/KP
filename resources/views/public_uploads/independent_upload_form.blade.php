<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Verifikasi berhasil! Silakan unggah gambar Anda.') }}
    </div>

    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('public.supplier.store_upload') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <x-input-label for="title" :value="__('Judul Gambar (Opsional)')" />
            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="supplier_reference_code" :value="__('Kode Referensi Anda (Opsional, ex: No. Faktur Anda)')" />
            <x-text-input id="supplier_reference_code" class="block mt-1 w-full" type="text" name="supplier_reference_code" :value="old('supplier_reference_code')" />
            <x-input-error :messages="$errors->get('supplier_reference_code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="image_file" :value="__('Pilih File Gambar')" />
            <input id="image_file" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" type="file" name="image_file" required />
            <x-input-error :messages="$errors->get('image_file')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Unggah Gambar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
