<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Verifikasi berhasil! Anda sekarang dapat mengunggah gambar.') }}
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

    <form method="POST" action="{{ route('supplier.upload.store') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <x-input-label for="title" :value="__('Judul Gambar (Opsional)')" />
            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="upload_code" :value="__('Kode Unik Gambar Anda')" />
            <x-text-input id="upload_code" class="block mt-1 w-full" type="text" name="upload_code" value="{{ old('upload_code', Str::random(8)) }}" readonly />
            <p class="text-xs text-gray-500 mt-1">Kode ini akan digunakan untuk mengidentifikasi gambar Anda. Simpan kode ini!</p>
            <x-input-error :messages="$errors->get('upload_code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="upload_image" :value="__('Pilih Gambar Faktur')" />
            <input id="upload_image" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" type="file" name="upload_image" required />
            <x-input-error :messages="$errors->get('upload_image')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Unggah Gambar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>