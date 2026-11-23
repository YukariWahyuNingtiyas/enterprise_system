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
        Schema::table('escm_purchase_orders', function (Blueprint $table) {
            $table->foreign(['inventory_id'], 'fk_po_inv')->references(['id'])->on('escm_inventory')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['vendor_id'], 'fk_po_vendor')->references(['id'])->on('escm_vendors')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escm_purchase_orders', function (Blueprint $table) {
            $table->dropForeign('fk_po_inv');
            $table->dropForeign('fk_po_vendor');
        });
    }
};
