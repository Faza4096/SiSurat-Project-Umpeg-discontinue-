<section>
    <h2 class="gov-card-title">Foto Profil</h2>
    <p class="gov-help">Opsional. Foto tampil pada menu akun di kanan atas dan pada bilah samping.</p>

    <div class="mt-4 flex flex-wrap items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gov-50 text-gov-600 ring-1 ring-inset ring-slate-200">
            @if ($foto = $user->fotoProfilUrl())
                <img src="{{ $foto }}" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover">
            @else
                <x-icon name="user" class="h-5 w-5" />
            @endif
        </span>

        <div class="min-w-0 flex-1">
            <form method="post" action="{{ route('profile.foto.unggah') }}" enctype="multipart/form-data" class="space-y-2">
                @csrf

                <label for="foto" class="gov-label">Pilih Berkas Foto</label>
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="gov-input !p-2 text-xs" required>
                <p class="gov-help">JPG, PNG, atau WEBP. Maksimal 2 MB; foto otomatis diperkecil menjadi maksimal 400 piksel.</p>
                <x-input-error class="mt-1.5 text-sm text-red-600" :messages="$errors->get('foto')" />

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button type="submit" class="gov-btn">
                        <x-icon name="check" class="h-4 w-4" /> Unggah Foto
                    </button>

                    @if (session('status') === 'foto-profil-diperbarui')
                        <p x-data="{ tampil: true }" x-show="tampil" x-transition x-init="setTimeout(() => tampil = false, 2500)"
                           class="text-[12px] font-semibold text-leaf-700">Foto tersimpan.</p>
                    @endif
                </div>
            </form>

            @if ($user->foto_profil)
                <form method="post" action="{{ route('profile.foto.hapus') }}" class="mt-2 flex items-center gap-3"
                      onsubmit="return confirm('Hapus foto profil?')">
                    @csrf
                    @method('delete')

                    <button type="submit" class="gov-btn-danger">
                        <x-icon name="close" class="h-4 w-4" /> Hapus Foto
                    </button>

                    @if (session('status') === 'foto-profil-dihapus')
                        <p x-data="{ tampil: true }" x-show="tampil" x-transition x-init="setTimeout(() => tampil = false, 2500)"
                           class="text-[12px] font-semibold text-leaf-700">Foto dihapus.</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</section>
