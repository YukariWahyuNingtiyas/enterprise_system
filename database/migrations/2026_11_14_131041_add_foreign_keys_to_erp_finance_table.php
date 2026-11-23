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
        Schema::table('erp_finance', function (Blueprint $table) {
            $table->foreign(['invoice_id'], 'fk_fin_inv')->references(['id'])->on('erp_invoices')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_fin_user')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_finance', function (Blueprint $table) {
            $table->dropForeign('fk_fin_inv');
            $table->dropForeign('fk_fin_user');
        });
    }
};
