    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                // Hapus kolom yang tidak lagi digunakan untuk OTP
                if (Schema::hasColumn('suppliers', 'otp_code')) {
                    $table->dropColumn('otp_code');
                }
                if (Schema::hasColumn('suppliers', 'otp_expires_at')) {
                    $table->dropColumn('otp_expires_at');
                }
            });
        }

        public function down(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('otp_code')->nullable();
                $table->timestamp('otp_expires_at')->nullable();
            });
        }
    };
    