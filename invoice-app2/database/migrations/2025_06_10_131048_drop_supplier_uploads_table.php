    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::dropIfExists('supplier_uploads');
        }

        public function down(): void
        {
            // Jika diperlukan, Anda bisa membuat ulang tabelnya di sini
            Schema::create('supplier_uploads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->string('filename');
                $table->string('filepath');
                $table->string('title')->nullable();
                $table->boolean('is_linked')->default(false);
                $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamps();
            });
        }
    };
    