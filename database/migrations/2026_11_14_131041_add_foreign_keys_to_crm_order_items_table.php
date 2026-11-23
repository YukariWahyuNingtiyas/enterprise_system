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
        Schema::table('crm_order_items', function (Blueprint $table) {
            $table->foreign(['inventory_id'], 'fk_oitem_inv')->references(['id'])->on('escm_inventory')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['order_id'], 'fk_oitem_order')->references(['id'])->on('crm_orders')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_order_items', function (Blueprint $table) {
            $table->dropForeign('fk_oitem_inv');
            $table->dropForeign('fk_oitem_order');
        });
    }
};
