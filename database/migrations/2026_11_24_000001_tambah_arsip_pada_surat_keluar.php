<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->timestamp('diarsipkan_pada')->nullable()->after('disetujui_pada');
            $table->foreignId('diarsipkan_oleh')->nullable()->after('diarsipkan_pada')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diarsipkan_oleh');
            $table->dropColumn('diarsipkan_pada');
        });
    }
};
