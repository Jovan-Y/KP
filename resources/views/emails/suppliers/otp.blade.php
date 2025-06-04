<x-mail::message>
# Kode Verifikasi OTP Anda

Halo,

Kode OTP Anda untuk mengunggah gambar adalah:

<x-mail::panel>
# {{ $otpCode }}
</x-mail::panel>

Kode ini akan kedaluwarsa pada **{{ $otpExpiresAt->format('H:i:s, d M Y') }} WIB**.

Mohon masukkan kode ini di halaman verifikasi dalam waktu 5 menit.

Jika Anda tidak meminta kode ini, abaikan email ini.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>