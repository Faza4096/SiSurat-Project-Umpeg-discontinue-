<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Foto profil bersifat opsional: pengguna boleh membiarkannya kosong. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('foto_profil')->nullable()->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('foto_profil');
        });
    }
};
