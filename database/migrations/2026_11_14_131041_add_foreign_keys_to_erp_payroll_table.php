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
        Schema::table('erp_payroll', function (Blueprint $table) {
            $table->foreign(['employee_id'], 'fk_pay_emp')->references(['id'])->on('erp_hr')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payroll', function (Blueprint $table) {
            $table->dropForeign('fk_pay_emp');
        });
    }
};
