<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_images', function (Blueprint $table) {
            // Tambahkan kolom 'type' setelah 'title'
            $table->string('type')->default('reference')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_images', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};