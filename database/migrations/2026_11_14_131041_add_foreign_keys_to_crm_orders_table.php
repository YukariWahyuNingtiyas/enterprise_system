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
        Schema::table('crm_orders', function (Blueprint $table) {
            $table->foreign(['customer_id'], 'fk_order_cust')->references(['id'])->on('crm_customers')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['invoice_id'], 'fk_order_invoice')->references(['id'])->on('erp_invoices')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_order_user')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_orders', function (Blueprint $table) {
            $table->dropForeign('fk_order_cust');
            $table->dropForeign('fk_order_invoice');
            $table->dropForeign('fk_order_user');
        });
    }
};
