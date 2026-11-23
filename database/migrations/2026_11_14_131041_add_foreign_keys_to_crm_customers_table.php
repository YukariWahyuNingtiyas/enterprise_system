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
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->foreign(['lead_id'], 'fk_cust_lead')->references(['id'])->on('crm_leads')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->dropForeign('fk_cust_lead');
        });
    }
};
