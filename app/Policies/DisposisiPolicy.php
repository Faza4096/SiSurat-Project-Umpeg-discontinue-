<?php

namespace App\Policies;

use App\Models\Disposisi;
use App\Models\User;

class DisposisiPolicy
{
    /** Surat induk terlihat, atau disposisi memang ditujukan ke unit pengguna. */
    public function view(User $user, Disposisi $disposisi): bool
    {
        if ($disposisi->bolehDilihatOleh($user)) {
            return true;
        }

        $surat = $disposisi->suratMasuk ?? $disposisi->suratKeluar;

        return $surat?->bolehDilihatOleh($user) ?? false;
    }

    /** Staf dan admin selalu boleh; unit tujuan boleh untuk disposisinya. */
    public function unduhKartu(User $user, Disposisi $disposisi): bool
    {
        return $disposisi->bolehDilihatOleh($user);
    }

    public function create(User $user): bool
    {
        return $user->punyaRole('staf', 'admin');
    }

    public function delete(User $user, Disposisi $disposisi): bool
    {
        return $user->punyaRole('staf', 'admin');
    }
}
