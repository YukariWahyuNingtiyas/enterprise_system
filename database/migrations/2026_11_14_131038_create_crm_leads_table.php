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
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->text('kebutuhan')->nullable();
            $table->decimal('anggaran', 15)->default(0);
            $table->integer('frekuensi_komunikasi')->default(0);
            $table->enum('status', ['prospect', 'negotiation', 'deal', 'lost'])->default('prospect')->index('idx_leads_status');
            $table->decimal('skor_prioritas', 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
