<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Mencabut sesi aktif pengguna agar perangkat yang sudah masuk tidak tetap
 * login setelah sandinya diganti. Hanya berlaku untuk driver sesi basis data;
 * driver lain (file/redis) memerlukan penanganan tersendiri.
 */
class SesiPenggunaService
{
    public function putuskan(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();
    }
}
