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
        Schema::table('erp_journals', function (Blueprint $table) {
            $table->foreign(['finance_id'], 'fk_journal_finance')->references(['id'])->on('erp_finance')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_journals', function (Blueprint $table) {
            $table->dropForeign('fk_journal_finance');
        });
    }
};
