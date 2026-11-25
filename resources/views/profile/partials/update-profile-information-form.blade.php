<section>
    <h2 class="gov-card-title">Informasi Profil</h2>
    <p class="gov-help">Nama dan email akun dinas.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="gov-label">Nama Lengkap</label>
            <input id="name" name="name" type="text" class="gov-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-1.5 text-sm text-red-600" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="gov-label">Email Dinas</label>
            <input id="email" name="email" type="email" class="gov-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-1.5 text-sm text-red-600" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] leading-relaxed text-amber-800">
                    <p>
                        Alamat email belum diverifikasi.
                        <button form="send-verification" class="font-semibold underline underline-offset-2">Kirim ulang tautan verifikasi</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 font-semibold">Tautan verifikasi baru sudah dikirim.</p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="current_password" class="gov-label">Kata Sandi Saat Ini</label>
            <input id="current_password" name="current_password" type="password" class="gov-input" autocomplete="current-password">
            <p class="gov-help mt-1">Wajib diisi bila email diubah.</p>
            <x-input-error class="mt-1.5 text-sm text-red-600" :messages="$errors->get('current_password')" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" class="gov-btn">
                <x-icon name="check" class="h-4 w-4" /> Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ tampil: true }" x-show="tampil" x-transition x-init="setTimeout(() => tampil = false, 2500)"
                   class="text-[12px] font-semibold text-leaf-700">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
