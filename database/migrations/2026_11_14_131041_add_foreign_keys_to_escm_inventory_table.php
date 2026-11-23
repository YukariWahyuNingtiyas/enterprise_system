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
        Schema::table('escm_inventory', function (Blueprint $table) {
            $table->foreign(['vendor_id'], 'fk_inventory_vendor')->references(['id'])->on('escm_vendors')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escm_inventory', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_vendor');
        });
    }
};
