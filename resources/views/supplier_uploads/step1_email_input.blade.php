<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Silakan masukkan email supplier Anda untuk mengunggah gambar faktur. Kode verifikasi (OTP) akan dikirimkan.') }}
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

    <form method="POST" action="{{ route('supplier.upload.send_otp') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Supplier')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Kirim Kode Verifikasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>