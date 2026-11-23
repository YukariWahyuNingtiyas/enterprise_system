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
        Schema::create('erp_payroll', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->char('periode', 7);
            $table->decimal('gaji_pokok', 15);
            $table->decimal('tunjangan', 15)->default(0);
            $table->decimal('potongan', 15)->default(0);
            $table->decimal('total_gaji', 15);
            $table->timestamps();

            $table->unique(['employee_id', 'periode'], 'uq_pay_emp_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_payroll');
    }
};
