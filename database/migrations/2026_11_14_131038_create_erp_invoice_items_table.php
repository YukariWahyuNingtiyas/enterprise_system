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
        Schema::create('erp_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->index('fk_item_inv');
            $table->string('nama_item', 150);
            $table->unsignedInteger('jumlah');
            $table->decimal('harga', 15);
            $table->decimal('subtotal', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_invoice_items');
    }
};
