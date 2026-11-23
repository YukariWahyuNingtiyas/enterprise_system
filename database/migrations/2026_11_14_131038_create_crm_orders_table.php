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
        Schema::create('crm_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomor_order', 30)->unique('nomor_order');
            $table->unsignedBigInteger('customer_id')->index('fk_order_cust');
            $table->unsignedBigInteger('invoice_id')->nullable()->unique('invoice_id');
            $table->enum('status', ['draft', 'dikonfirmasi', 'dibatalkan'])->default('draft');
            $table->enum('status_pengiriman', ['menunggu', 'diproses', 'dikirim', 'diterima'])->default('menunggu');
            $table->string('no_resi', 50)->nullable();
            $table->date('tanggal');
            $table->decimal('total', 15)->default(0);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('fk_order_user');
            $table->timestamps();

            $table->index(['status', 'status_pengiriman'], 'idx_order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_orders');
    }
};
