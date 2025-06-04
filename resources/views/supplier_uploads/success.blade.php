<x-guest-layout>
    <div class="mb-4 text-sm text-green-600">
        {{ session('success') }}
    </div>

    <h2 class="text-2xl font-bold mb-4">Gambar Anda Berhasil Diunggah!</h2>
    <p class="mb-4 text-gray-700">Terima kasih telah mengunggah gambar Anda. Gambar Anda telah berhasil disimpan.</p>

    @if($supplierUpload)
        <div class="border rounded-lg overflow-hidden shadow-sm p-4 mb-4">
            <h3 class="text-lg font-bold mb-2">Detail Gambar Anda:</h3>
            <p><strong>Kode Gambar:</strong> {{ $supplierUpload->upload_code }}</p>
            <p><strong>Judul:</strong> {{ $supplierUpload->title ?? 'Tidak Ada Judul' }}</p>
            <p><strong>File:</strong> {{ $supplierUpload->filename }}</p>

            <div class="mt-4 text-center">
                <img src="{{ Storage::url($supplierUpload->filepath) }}" alt="{{ $supplierUpload->title ?? $supplierUpload->filename }}" class="max-w-full h-auto mx-auto border rounded-md">
            </div>
        </div>
    @endif

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Anda bisa kembali ke halaman login.
            <a href="{{ route('login') }}" class="underline text-sm text-gray-600 hover:text-gray-900">Kembali ke halaman Login</a>
        </p>
    </div>
</x-guest-layout>