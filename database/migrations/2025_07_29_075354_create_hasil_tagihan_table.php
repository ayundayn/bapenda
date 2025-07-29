<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hasil_tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('nop_bank')->nullable();
            $table->double('nominal_bank')->default(0);
            $table->string('nop_vtax')->nullable();
            $table->double('nominal_vtax')->default(0);
            $table->double('selisih')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_tagihan');
    }
};