<?php

namespace App\Policies;

use App\Models\Lampiran;
use App\Models\User;

class LampiranPolicy
{
    /** Unduh lampiran mengikuti aturan akses surat induknya. */
    public function unduh(User $user, Lampiran $lampiran): bool
    {
        $surat = $lampiran->surat;

        return $surat !== null && $user->can('view', $surat);
    }

    public function hapus(User $user, Lampiran $lampiran): bool
    {
        $surat = $lampiran->surat;

        return $surat !== null
            && $user->can('kelolaLampiran', $surat);
    }
}
