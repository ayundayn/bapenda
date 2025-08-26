<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('hasil_tagihan', function (Blueprint $table) {
            $table->string('sheet_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('hasil_tagihan', function (Blueprint $table) {
            $table->dropColumn('sheet_name');
        });
    }
};
