<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Empat peran: administrator, petugas TU, pimpinan, dan staf/umpeg. */
    public function up(): void
    {
        foreach (['admin', 'petugas_tu', 'pimpinan', 'staf'] as $nama) {
            DB::table('roles')->updateOrInsert(['nama' => $nama], ['nama' => $nama]);
        }
    }

    public function down(): void
    {
        $staf = DB::table('roles')->where('nama', 'staf')->value('id');
        $lama = DB::table('roles')->whereIn('nama', ['petugas_tu', 'pimpinan'])->pluck('id');

        if ($staf !== null && $lama->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $lama)->update(['role_id' => $staf]);
            DB::table('roles')->whereIn('id', $lama)->delete();
        }
    }
};
