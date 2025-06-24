@component('mail::message')
{{-- Bagian Logo Kustom --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
@endcomponent
@endslot

{{-- Isi Email --}}
# Halo, {{ $user->name }}!

Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.

@component('mail::button', ['url' => $resetUrl])
Atur Ulang Kata Sandi
@endcomponent

Tautan pengaturan ulang kata sandi ini akan kedaluwarsa dalam **{{ $expire }} menit**.

Jika Anda tidak meminta pengaturan ulang kata sandi, tidak ada tindakan lebih lanjut yang diperlukan.

{{-- Footer (tidak ada salam atau nama "Laravel") --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent