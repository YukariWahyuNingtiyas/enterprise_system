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
        Schema::create('escm_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_barang', 20)->unique('kode_barang');
            $table->string('nama', 100);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(10);
            $table->decimal('harga_jual', 15)->default(0);
            $table->string('lokasi_rak', 20)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable()->index('fk_inventory_vendor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escm_inventory');
    }
};
