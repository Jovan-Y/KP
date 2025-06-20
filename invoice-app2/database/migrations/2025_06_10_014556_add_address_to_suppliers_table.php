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
            Schema::table('suppliers', function (Blueprint $table) {
                // Menambahkan kolom 'address' setelah kolom 'company_name'
                $table->text('address')->nullable()->after('company_name');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                // Menghapus kolom 'address' jika migrasi di-rollback
                $table->dropColumn('address');
            });
        }
    };
    