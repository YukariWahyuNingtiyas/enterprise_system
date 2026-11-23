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
        Schema::create('crm_communications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lead_id')->index('fk_comm_lead');
            $table->enum('channel', ['telepon', 'email', 'whatsapp', 'tatap_muka']);
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_communications');
    }
};
