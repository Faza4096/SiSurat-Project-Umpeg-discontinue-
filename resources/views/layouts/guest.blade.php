@props(['judul' => 'Masuk'])

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judul }} — SiSurat Kabupaten Bogor</title>
    <link rel="icon" type="image/png" href="{{ asset('img/lambang-kabupaten-bogor.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800">
<div class="fixed inset-x-0 top-0 z-50 h-[3px] bg-gradient-to-r from-gov-950 via-gov-600 to-gov-800" aria-hidden="true">
    <div class="anim-rule h-[3px] w-28 bg-gold-400"></div>
</div>

<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- Latar foto kantor dengan lapisan navy agar teks putih tetap terbaca. --}}
    <section class="relative flex min-h-[320px] flex-col justify-between overflow-hidden bg-gov-900 px-7 py-9 text-gov-100 sm:px-8 lg:min-h-0 lg:w-[46%] lg:px-12 lg:py-14"
             style="background-image: linear-gradient(180deg, rgba(8, 37, 34, 0.74), rgba(8, 37, 34, 0.90)), url('{{ asset('img/login-latar.webp') }}'); background-size: cover; background-position: center;">

        <div class="relative">
            <div class="flex items-center gap-4">
                <x-lambang class="h-14 w-14 shrink-0" />
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-300">{{ $identitas->nama }}</p>
                    <p class="font-display text-[17px] font-semibold leading-snug tracking-[-0.015em] text-white">{{ $identitas->namaUnit() }}</p>
                </div>
            </div>

            <div class="gov-rule-light mt-4 max-w-sm lg:mt-5" aria-hidden="true"></div>
        </div>

        <div class="relative my-auto max-w-lg pt-6">
            <h1 class="font-display text-[26px] font-bold leading-[1.2] tracking-[-0.03em] text-white sm:text-3xl lg:text-[2.35rem]">
                Registrasi &amp; Disposisi<br><span class="text-gold-300">Surat Dinas Elektronik</span>
            </h1>
            <p class="mt-4 hidden text-sm leading-relaxed text-gov-100/80 lg:block">
                Pencatatan surat masuk dan keluar, disposisi, tindak lanjut, serta arsip dalam satu aplikasi.
            </p>
        </div>

        <div class="relative mt-10 hidden lg:block">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-gold-300/80">Layanan</p>
            <ul class="mt-3 grid gap-px overflow-hidden border-y border-white/10 bg-white/10 text-sm text-gov-100/85 sm:grid-cols-2">
                <li class="flex items-start gap-2.5 bg-gov-950 px-4 py-3">
                    <x-icon name="mail-in" class="mt-0.5 h-4 w-4 shrink-0 text-gold-300" /> Nomor agenda otomatis per tahun
                </li>
                <li class="flex items-start gap-2.5 bg-gov-950 px-4 py-3">
                    <x-icon name="print" class="mt-0.5 h-4 w-4 shrink-0 text-gold-300" /> Kartu disposisi siap cetak
                </li>
                <li class="flex items-start gap-2.5 bg-gov-950 px-4 py-3">
                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-gold-300" /> Pelacakan status tindak lanjut
                </li>
                <li class="flex items-start gap-2.5 bg-gov-950 px-4 py-3">
                    <x-icon name="document" class="mt-0.5 h-4 w-4 shrink-0 text-gold-300" /> Arsip scan surat terpusat
                </li>
            </ul>
        </div>
    </section>

    {{-- Di layar HP kartu form mulai dari atas agar tidak menyisakan ruang kosong besar. --}}
    <section class="flex flex-1 items-start justify-center px-5 py-10 sm:px-8 lg:items-center lg:py-12">
        <div class="anim-rise w-full max-w-md">
            <div class="gov-card overflow-hidden">
                <div class="gov-rule" aria-hidden="true"></div>
                <div class="p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-6 text-center text-[11px] leading-relaxed text-slate-400">
                &copy; {{ now()->year }} {{ $identitas->nama }} · {{ $identitas->namaUnit() }}<br>
                Gunakan akun dinas terdaftar. Aktivitas masuk tercatat pada log sistem.
            </p>
        </div>
    </section>
</div>
</body>
</html>
