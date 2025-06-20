<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Hapus kolom yang tidak diperlukan lagi
            $table->dropColumn(['time_zone', 'payment_method', 'shipping_cost', 'has_ppn', 'ppn_type']);

            // Tambahkan kolom baru
            $table->string('po_number')->nullable()->after('invoice_number');
            $table->decimal('ppn_percentage', 5, 2)->default(0.00)->after('discount');
            $table->json('other_taxes')->nullable()->after('ppn_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Logika untuk rollback jika diperlukan
            $table->string('time_zone', 10)->nullable();
            $table->enum('payment_method', ['cash', 'credit'])->nullable();
            $table->double('shipping_cost', 10, 2)->default(0);
            $table->boolean('has_ppn')->default(false);
            $table->enum('ppn_type', ['included', 'excluded'])->nullable();

            $table->dropColumn(['po_number', 'ppn_percentage', 'other_taxes']);
        });
    }
};
