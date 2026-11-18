<?php

namespace App\Policies;

use App\Models\Disposisi;
use App\Models\User;

class DisposisiPolicy
{
    public function view(User $user, Disposisi $disposisi): bool
    {
        $surat = $disposisi->suratMasuk ?? $disposisi->suratKeluar;

        return $surat?->bolehDilihatOleh($user) ?? false;
    }

    /** Staf dan admin selalu boleh; unit tujuan boleh untuk disposisinya. */
    public function unduhKartu(User $user, Disposisi $disposisi): bool
    {
        if ($user->punyaRole('admin', 'staf')) {
            return true;
        }

        return $user->unit_id !== null
            && $disposisi->tujuan()->where('unit_id', $user->unit_id)->exists();
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
