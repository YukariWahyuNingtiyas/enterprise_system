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
        Schema::create('ai_analytics_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('modul', ['crm', 'erp', 'escm']);
            $table->enum('jenis_analisis', ['lead_scoring', 'anomali_keuangan', 'prediksi_stok']);
            $table->string('ref_tabel', 50)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->decimal('skor')->nullable();
            $table->text('hasil')->nullable();
            $table->timestamps();

            $table->index(['ref_tabel', 'ref_id'], 'idx_ai_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analytics_logs');
    }
};
