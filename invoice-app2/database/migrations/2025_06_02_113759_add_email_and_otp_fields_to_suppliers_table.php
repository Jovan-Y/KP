<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('name'); // Tambah kolom email
            $table->string('otp_code')->nullable()->after('company_name'); // Tambah kolom OTP
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code'); // Tambah kolom expired OTP
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['email', 'otp_code', 'otp_expires_at']);
        });
    }
};