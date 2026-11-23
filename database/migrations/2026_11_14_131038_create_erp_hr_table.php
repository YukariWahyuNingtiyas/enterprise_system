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
        Schema::create('erp_hr', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_karyawan', 20)->unique('kode_karyawan');
            $table->string('nama', 100);
            $table->string('email', 100)->unique('email');
            $table->string('telepon', 20)->nullable();
            $table->string('jabatan', 50);
            $table->string('bagian', 50);
            $table->date('tanggal_bergabung');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_hr');
    }
};
