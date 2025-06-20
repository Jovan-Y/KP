<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('upload_code')->unique(); // Kode unik untuk gambar yang diunggah supplier
            $table->string('title')->nullable(); // Judul gambar dari supplier
            $table->string('filename'); // Nama file gambar
            $table->string('filepath'); // Path penyimpanan gambar
            $table->boolean('is_linked')->default(false); // Status apakah sudah dikaitkan ke faktur
            $table->foreignId('linked_invoice_id')->nullable()->constrained('invoices')->onDelete('set null'); // ID faktur jika sudah dikaitkan
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null'); // Supplier pengunggah (opsional, jika kita bisa tentukan dari email)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_uploads');
    }
};