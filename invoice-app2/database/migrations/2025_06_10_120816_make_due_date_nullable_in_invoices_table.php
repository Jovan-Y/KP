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
            // Mengubah kolom due_date agar bisa bernilai NULL
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('due_date')->nullable()->change();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('invoices', function (Blueprint $table) {
                // Mengembalikan ke keadaan semula jika di-rollback (tidak bisa NULL)
                $table->date('due_date')->nullable(false)->change();
            });
        }
    };
    