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
        Schema::table('erp_invoices', function (Blueprint $table) {
            $table->foreign(['customer_id'], 'fk_inv_cust')->references(['id'])->on('crm_customers')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoices', function (Blueprint $table) {
            $table->dropForeign('fk_inv_cust');
        });
    }
};
