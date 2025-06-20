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
                // Mengubah kolom company_name agar tidak bisa null
                $table->string('company_name')->nullable(false)->change();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('suppliers', function (Blueprint $table) {
                // Mengembalikan kolom ke keadaan semula jika migrasi di-rollback
                $table->string('company_name')->nullable()->change();
            });
        }
    };
    