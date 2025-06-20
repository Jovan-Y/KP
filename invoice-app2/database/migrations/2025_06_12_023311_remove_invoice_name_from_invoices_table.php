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
        Schema::table('invoices', function (Blueprint $table) {
            // Hapus kolom invoice_name jika ada
            if (Schema::hasColumn('invoices', 'invoice_name')) {
                $table->dropColumn('invoice_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Tambahkan kembali kolom untuk rollback
            $table->string('invoice_name')->after('supplier_id');
        });
    }
};
