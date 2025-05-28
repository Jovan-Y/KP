<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('invoice_name');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('received_date');
            $table->string('time_zone', 10); // WIB, WIT, WITA
            $table->enum('payment_method', ['cash', 'credit']);
            $table->date('due_date');
            $table->boolean('has_ppn')->default(false);
            $table->enum('ppn_type', ['included', 'excluded'])->nullable(); // if has_ppn is true
            $table->double('subtotal_items', 10, 2); // Total subtotal dari semua item
            $table->double('discount', 10, 2)->default(0);
            $table->double('shipping_cost', 10, 2)->default(0);
            $table->double('ppn_amount', 10, 2)->default(0); // Jumlah PPN yang dihitung
            $table->double('total_amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->softDeletes(); // Untuk soft delete
            $table->string('public_code', 32)->unique()->nullable(); // Untuk akses gambar tanpa akun
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};