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
        // kalau tabel hasil_upload ada → tambah kolom
        if (Schema::hasTable('hasil_upload')) {
            Schema::table('hasil_upload', function (Blueprint $table) {
                if (!Schema::hasColumn('hasil_upload', 'tanggal')) {
                    $table->date('tanggal')->nullable()->after('status');
                }
            });
        }

        // kalau tabel hasil_upload belum ada → buat tabel baru sekaligus kolom tanggal
        else {
            Schema::create('hasil_upload', function (Blueprint $table) {
                $table->id();
                $table->string('nama_file')->nullable();
                $table->string('status')->nullable();
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hasil_upload') && Schema::hasColumn('hasil_upload', 'tanggal')) {
            Schema::table('hasil_upload', function (Blueprint $table) {
                $table->dropColumn('tanggal');
            });
        }
    }
};
