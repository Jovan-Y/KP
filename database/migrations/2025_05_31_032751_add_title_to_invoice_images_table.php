<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_images', function (Blueprint $table) {
            $table->string('title')->nullable()->after('filepath'); // Tambahkan kolom title, bisa null
        });
    }

    public function down(): void
    {
        Schema::table('invoice_images', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};