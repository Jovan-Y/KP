    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                // Kolom untuk menyimpan detail pembayaran sebagai JSON
                $table->json('payment_details')->nullable()->after('address');
            });
        }

        public function down(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('payment_details');
            });
        }
    };
    