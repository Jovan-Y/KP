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
                // Menambahkan kolom untuk tipe pembayaran setelah due_date
                $table->string('payment_type')->nullable()->after('due_date');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('payment_type');
            });
        }
    };
    