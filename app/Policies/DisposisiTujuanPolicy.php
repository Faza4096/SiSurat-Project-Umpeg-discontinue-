<?php

namespace App\Policies;

use App\Models\DisposisiTujuan;
use App\Models\User;

class DisposisiTujuanPolicy
{
    public function view(User $user, DisposisiTujuan $tujuan): bool
    {
        return $tujuan->disposisi?->suratMasuk?->bolehDilihatOleh($user) ?? false;
    }

    /** Staf boleh mengubah tujuan mana pun; selain itu hanya unit tujuan sendiri. */
    public function ubah(User $user, DisposisiTujuan $tujuan): bool
    {
        return $user->punyaRole('staf') || $tujuan->bolehDiubahOleh($user);
    }
}
