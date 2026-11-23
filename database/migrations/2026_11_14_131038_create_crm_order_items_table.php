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
        Schema::create('crm_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index('fk_oitem_order');
            $table->unsignedBigInteger('inventory_id')->index('fk_oitem_inv');
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
        Schema::dropIfExists('crm_order_items');
    }
};
