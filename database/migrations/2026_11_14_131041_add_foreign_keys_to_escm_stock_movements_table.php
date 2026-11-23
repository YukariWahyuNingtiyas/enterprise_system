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
        Schema::table('escm_stock_movements', function (Blueprint $table) {
            $table->foreign(['inventory_id'], 'fk_move_inv')->references(['id'])->on('escm_inventory')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['created_by'], 'fk_move_user')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escm_stock_movements', function (Blueprint $table) {
            $table->dropForeign('fk_move_inv');
            $table->dropForeign('fk_move_user');
        });
    }
};
