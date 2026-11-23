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
        Schema::create('erp_journals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('finance_id')->index('fk_journal_finance');
            $table->string('akun_debit', 60);
            $table->string('akun_kredit', 60);
            $table->decimal('nominal', 15);
            $table->date('tanggal')->index('idx_journal_tgl');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_journals');
    }
};
