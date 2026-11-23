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
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable()->index('fk_cust_lead');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_customers');
    }
};
