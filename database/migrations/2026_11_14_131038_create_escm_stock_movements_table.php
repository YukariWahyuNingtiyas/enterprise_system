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
        Schema::create('escm_stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_id');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->unsignedInteger('jumlah');
            $table->string('referensi', 50)->nullable();
            $table->string('keterangan')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('created_by')->nullable()->index('fk_move_user');
            $table->timestamps();

            $table->index(['inventory_id', 'tanggal'], 'idx_move_inv_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escm_stock_movements');
    }
};
