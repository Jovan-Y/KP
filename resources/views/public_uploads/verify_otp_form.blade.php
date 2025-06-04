<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Kode verifikasi (OTP) telah dikirimkan ke email Anda (') }}{{ $supplierEmail ?? 'email' }}{{__('). Mohon masukkan kode tersebut di bawah.') }}
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

    <form method="POST" action="{{ route('public.supplier.verify_otp') }}">
        @csrf

        <div>
            <x-input-label for="otp" :value="__('Kode OTP')" />
            <x-text-input id="otp" class="block mt-1 w-full" type="text" name="otp" :value="old('otp')" required autofocus />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verifikasi Kode') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>