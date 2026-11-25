<section>
    <h2 class="gov-card-title">Kata Sandi</h2>
    <p class="gov-help">Gunakan kata sandi panjang yang tidak dipakai di layanan lain.</p>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="gov-label">Kata Sandi Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="gov-input" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5 text-sm text-red-600" />
        </div>

        <div>
            <label for="update_password_password" class="gov-label">Kata Sandi Baru</label>
            <input id="update_password_password" name="password" type="password" class="gov-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5 text-sm text-red-600" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="gov-label">Ulangi Kata Sandi Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="gov-input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5 text-sm text-red-600" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" class="gov-btn">
                <x-icon name="check" class="h-4 w-4" /> Simpan Kata Sandi
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ tampil: true }" x-show="tampil" x-transition x-init="setTimeout(() => tampil = false, 2500)"
                   class="text-[12px] font-semibold text-leaf-700">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
