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
        Schema::create('erp_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomor_invoice', 30)->unique('nomor_invoice');
            $table->unsignedBigInteger('customer_id')->index('fk_inv_cust');
            $table->date('tanggal_terbit');
            $table->date('jatuh_tempo');
            $table->decimal('total', 15)->default(0);
            $table->enum('status', ['draft', 'diterbitkan', 'lunas', 'jatuh_tempo', 'dibatalkan'])->default('draft')->index('idx_inv_status');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_invoices');
    }
};
