<x-mail::message>
# Halo Supplier,

Terima kasih telah melakukan transaksi dengan kami.

Berikut adalah detail faktur Anda dan kode unik untuk mengunggah gambar-gambar terkait faktur ini:

**Nama Faktur:** {{ $invoice->invoice_name }}
**Nomor Faktur:** {{ $invoice->invoice_number }}
**Tanggal Faktur:** {{ $invoice->invoice_date->format('d M Y') }}
**Total Faktur:** Rp {{ number_format($invoice->total_amount, 2, ',', '.') }}

---

### Kode Unik Anda untuk Mengunggah Gambar:
**{{ $invoice->public_code }}**

Silakan gunakan kode ini untuk mengunggah gambar faktur atau dokumen lainnya di halaman berikut:

<x-mail::button :url="route('public.upload.form')">
Unggah Gambar Faktur
</x-mail::button>

Mohon masukkan kode unik di atas pada formulir yang tersedia.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>