<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_uploads', function (Blueprint $table) {
            // Tambahkan kolom invoice_id sebagai foreign key ke tabel invoices
            // Kolom ini bisa null karena gambar awalnya belum terkait
            // onDelete('set null') berarti jika faktur dihapus, kolom ini akan menjadi NULL,
            // tetapi unggahan gambar tidak ikut terhapus.
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->after('is_linked') // Menempatkan kolom setelah kolom 'is_linked'
                  ->constrained('invoices')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_uploads', function (Blueprint $table) {
            // Hapus foreign key constraint sebelum menghapus kolom
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};