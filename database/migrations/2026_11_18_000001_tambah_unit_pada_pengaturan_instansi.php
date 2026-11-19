<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unit kerja pemilik portal, dipakai di sidebar, footer, halaman login, dan
 * judul PDF laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_instansi', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_instansi', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
