<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('filepath'); // path relatif atau absolut
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_images');
    }
};