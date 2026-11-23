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
        Schema::create('erp_finance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_transaksi', 30)->unique('kode_transaksi');
            $table->unsignedBigInteger('invoice_id')->nullable()->unique('invoice_id');
            $table->enum('jenis', ['pemasukan', 'pengeluaran'])->index('idx_fin_jenis');
            $table->string('kategori', 50);
            $table->decimal('nominal', 15);
            $table->date('tanggal')->index('idx_fin_tgl');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('fk_fin_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_finance');
    }
};
