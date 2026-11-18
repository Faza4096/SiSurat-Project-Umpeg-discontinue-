<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Dua peran saja: administrator dan staf (operator tunggal). */
    public function up(): void
    {
        foreach (['admin', 'staf'] as $nama) {
            DB::table('roles')->updateOrInsert(['nama' => $nama], ['nama' => $nama]);
        }

        $staf = DB::table('roles')->where('nama', 'staf')->value('id');
        $lama = DB::table('roles')->whereIn('nama', ['petugas_tu', 'pimpinan'])->pluck('id');

        if ($lama->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $lama)->update(['role_id' => $staf]);
            DB::table('roles')->whereIn('id', $lama)->delete();
        }
    }

    public function down(): void
    {
        foreach (['petugas_tu', 'pimpinan'] as $nama) {
            DB::table('roles')->updateOrInsert(['nama' => $nama], ['nama' => $nama]);
        }
    }
};
