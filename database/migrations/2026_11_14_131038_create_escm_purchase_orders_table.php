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
        Schema::create('escm_purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomor_po', 30)->unique('nomor_po');
            $table->unsignedBigInteger('vendor_id')->index('fk_po_vendor');
            $table->unsignedBigInteger('inventory_id')->index('fk_po_inv');
            $table->unsignedInteger('jumlah');
            $table->enum('status', ['draft', 'dipesan', 'diterima', 'dibatalkan'])->default('draft');
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escm_purchase_orders');
    }
};
