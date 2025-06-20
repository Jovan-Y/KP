    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                // Tambahkan kolom untuk tipe diskon (persen atau tetap)
                $table->string('discount_type')->default('fixed')->after('discount');
                // Ubah nama kolom 'discount' menjadi 'discount_value' untuk kejelasan
                $table->renameColumn('discount', 'discount_value');
            });
        }

        public function down(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                // Logika untuk rollback jika diperlukan
                $table->renameColumn('discount_value', 'discount');
                $table->dropColumn('discount_type');
            });
        }
    };
    