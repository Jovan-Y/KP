<x-guest-layout>
    <div class="mb-4 text-sm text-green-600">
        {{ session('success') }}
    </div>

    <h2 class="text-2xl font-bold mb-4">Gambar Berhasil Diunggah!</h2>
    <p class="mb-4 text-gray-700">Terima kasih atas unggahan Anda. Gambar Anda akan segera direview oleh tim kami.</p>

    <div class="mt-6 text-center">
        <a href="{{ route('public.supplier.upload_form') }}" class="underline text-sm text-gray-600 hover:text-gray-900">Unggah Gambar Lain</a>
        <span class="text-gray-600 mx-2">|</span>
        <a href="{{ route('login') }}" class="underline text-sm text-gray-600 hover:text-gray-900">Kembali ke Halaman Login</a>
    </div>
</x-guest-layout>