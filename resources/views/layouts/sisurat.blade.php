<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Dashboard') — SiSurat Kabupaten Bogor</title>
    <link rel="icon" type="image/png" href="{{ asset('img/lambang-kabupaten-bogor.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<a href="#isi" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-gov-800 focus:shadow-gov">
    Lompat ke konten
</a>

<div class="fixed inset-x-0 top-0 z-50 h-[3px] bg-gov-900 no-print" aria-hidden="true">
    <div class="h-[3px] w-28 bg-gold-400"></div>
</div>

<div class="flex min-h-screen" x-data="{ sidebar: false, profil: false }">

    <div x-cloak x-show="sidebar" x-transition.opacity class="fixed inset-0 z-30 bg-gov-950/50 lg:hidden" @click="sidebar = false"></div>

    <aside class="fixed inset-y-0 left-0 z-40 flex w-[272px] -translate-x-full flex-col bg-gov-900 text-gov-100 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
        <div class="border-b border-white/10 px-5 pb-4 pt-5">
            <div class="flex items-start gap-3">
                <x-lambang class="h-11 w-11 shrink-0" />
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] font-semibold uppercase leading-[1.6] tracking-[0.1em] text-gold-300/90">{{ $identitas->nama }}</p>
                    <p class="font-serif text-[21px] font-semibold leading-tight text-white">SiSurat</p>
                </div>
                <button type="button" class="-mr-1 p-1 text-gov-200 transition hover:text-white lg:hidden" @click="sidebar = false" aria-label="Tutup menu">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>
            <p class="mt-2 text-[10px] uppercase tracking-[0.14em] text-gov-300/70">Tata Naskah Dinas</p>
            <div class="mt-3 h-px bg-gradient-to-r from-gold-400/80 via-gold-400/25 to-transparent" aria-hidden="true"></div>
        </div>

        <nav class="gov-scroll flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Menu utama">
            <div>
                <p class="px-3 pb-2 text-[9.5px] font-bold uppercase tracking-[0.18em] text-gov-300/60">Utama</p>
                <div class="space-y-0.5">
                    <a href="{{ route('dashboard') }}" class="gov-nav {{ request()->routeIs('dashboard') ? 'gov-nav-active' : '' }}">
                        <x-icon name="dashboard" class="h-[18px] w-[18px] shrink-0" /> Dashboard
                    </a>
                    <a href="{{ route('surat-masuk.index') }}" class="gov-nav {{ request()->routeIs('surat-masuk.*') ? 'gov-nav-active' : '' }}">
                        <x-icon name="mail-in" class="h-[18px] w-[18px] shrink-0" /> Surat Masuk
                    </a>
                    <a href="{{ route('surat-keluar.index') }}" class="gov-nav {{ request()->routeIs('surat-keluar.*') ? 'gov-nav-active' : '' }}">
                        <x-icon name="mail-out" class="h-[18px] w-[18px] shrink-0" /> Surat Keluar
                    </a>
                    <a href="{{ route('disposisi.index') }}" class="gov-nav {{ request()->routeIs('disposisi.*') ? 'gov-nav-active' : '' }}">
                        <x-icon name="forward" class="h-[18px] w-[18px] shrink-0" /> Disposisi
                    </a>
                    <a href="{{ route('laporan.index') }}" class="gov-nav {{ request()->routeIs('laporan.*') ? 'gov-nav-active' : '' }}">
                        <x-icon name="chart" class="h-[18px] w-[18px] shrink-0" /> Laporan
                    </a>
                </div>
            </div>

            @if (auth()->user()->punyaRole('admin'))
                <div>
                    <p class="px-3 pb-2 text-[9.5px] font-bold uppercase tracking-[0.18em] text-gov-300/60">Administrasi</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('pengguna.index') }}" class="gov-nav {{ request()->routeIs('pengguna.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="users" class="h-[18px] w-[18px] shrink-0" /> Pengguna
                        </a>
                        <a href="{{ route('unit-kerja.index') }}" class="gov-nav {{ request()->routeIs('unit-kerja.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="unit" class="h-[18px] w-[18px] shrink-0" /> Unit Kerja
                        </a>
                        <a href="{{ route('klasifikasi.index') }}" class="gov-nav {{ request()->routeIs('klasifikasi.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="tag" class="h-[18px] w-[18px] shrink-0" /> Klasifikasi
                        </a>
                        <a href="{{ route('instansi.index') }}" class="gov-nav {{ request()->routeIs('instansi.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="building" class="h-[18px] w-[18px] shrink-0" /> Instansi
                        </a>
                        <a href="{{ route('log.index') }}" class="gov-nav {{ request()->routeIs('log.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="log" class="h-[18px] w-[18px] shrink-0" /> Log Aktivitas
                        </a>
                        <a href="{{ route('pengaturan.edit') }}" class="gov-nav {{ request()->routeIs('pengaturan.*') ? 'gov-nav-active' : '' }}">
                            <x-icon name="building" class="h-[18px] w-[18px] shrink-0" /> Pengaturan Instansi
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="border-t border-white/10 px-3 py-3">
            <div class="flex items-center gap-2.5 px-1 py-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-gov-800 text-[11px] font-bold text-gold-300 ring-1 ring-inset ring-white/15">
                    {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($k) => mb_substr($k, 0, 1))->take(2)->implode('') }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10.5px] text-gov-300">{{ \Illuminate\Support\Str::headline(auth()->user()->role->nama ?? '-') }}{{ auth()->user()->unit ? ' · ' . auth()->user()->unit->nama : '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-md border border-white/15 px-3 py-2 text-[12.5px] font-semibold text-red-200 transition hover:border-red-400/40 hover:bg-red-500/10 hover:text-red-100">
                    <x-icon name="logout" class="h-4 w-4" /> Keluar
                </button>
            </form>
            <p class="mt-2.5 text-center text-[10px] tracking-wide text-gov-300/50">{{ $identitas->namaUnit() }} · {{ now()->year }}</p>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur no-print">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" class="rounded-md border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 lg:hidden" @click="sidebar = true" aria-label="Buka menu">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-serif text-[16px] font-semibold leading-tight text-gov-900 sm:hidden">SiSurat</p>
                    <p class="hidden truncate text-[9.5px] font-semibold uppercase tracking-[0.14em] text-slate-500 sm:block">{{ $identitas->nama }}</p>
                    <nav class="mt-0.5 hidden sm:block" aria-label="Remah roti">
                        <ol class="flex min-w-0 items-center gap-1.5 text-[12px]">
                            @if (request()->routeIs('dashboard'))
                                <li class="font-semibold text-gov-800" aria-current="page">Beranda</li>
                            @else
                                <li><a href="{{ route('dashboard') }}" class="text-slate-500 transition hover:text-gov-700 hover:underline hover:underline-offset-2">Beranda</a></li>
                                <li aria-hidden="true" class="text-slate-300">›</li>
                                <li class="truncate font-semibold text-gov-800" aria-current="page">@yield('judul')</li>
                            @endif
                        </ol>
                    </nav>
                </div>

                @php $belumDibaca = auth()->user()->unreadNotifications()->count(); @endphp
                <a href="{{ route('notifikasi.index') }}" title="Notifikasi"
                   class="relative rounded-md border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 hover:text-gov-700">
                    <x-icon name="bell" class="h-[18px] w-[18px]" />
                    @if ($belumDibaca > 0)
                        <span class="absolute -right-1.5 -top-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                            {{ $belumDibaca > 9 ? '9+' : $belumDibaca }}
                        </span>
                    @endif
                </a>

                <p class="hidden items-center gap-2 border-l border-slate-200 pl-3 text-[11.5px] text-slate-500 md:flex">
                    <x-icon name="calendar" class="h-3.5 w-3.5 text-gov-500" />
                    <span class="tabular-nums">{{ now()->translatedFormat('l, d F Y') }}</span>
                </p>

                <div class="relative">
                    <button type="button" class="flex items-center gap-2 rounded-md border border-slate-200 py-1.5 pl-1.5 pr-2 text-left transition hover:bg-slate-50" @click="profil = !profil">
                        <span class="flex h-7 w-7 items-center justify-center rounded bg-gov-700 text-[11px] font-bold text-white">
                            {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($k) => mb_substr($k, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="hidden sm:block">
                            <span class="block max-w-[10rem] truncate text-[12px] font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                            <span class="block text-[9.5px] uppercase tracking-wide text-slate-400">{{ str_replace('_', ' ', auth()->user()->role->nama ?? '-') }}</span>
                        </span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>

                    <div x-cloak x-show="profil" x-transition @click.outside="profil = false"
                         class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-gov">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-[13px] font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[11.5px] text-slate-500">{{ auth()->user()->email }}</p>
                            @if (auth()->user()->nip)
                                <p class="mt-1 text-[11px] text-slate-400">NIP {{ auth()->user()->nip }}</p>
                            @endif
                        </div>
                        <a href="{{ route('notifikasi.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50">
                            <x-icon name="bell" class="h-4 w-4" /> Notifikasi
                            @if ($belumDibaca > 0)
                                <span class="ml-auto rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $belumDibaca }}</span>
                            @endif
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50">
                            <x-icon name="user" class="h-4 w-4" /> Profil Saya
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 border-t border-slate-100 px-4 py-2.5 text-left text-[13px] text-red-600 hover:bg-red-50">
                                <x-icon name="logout" class="h-4 w-4" /> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main id="isi" class="mx-auto w-full max-w-[1360px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('ok'))
                <div class="mb-5 flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">
                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
                    <p>{{ session('ok') }}</p>
                </div>
            @endif

            @if (session('err'))
                <div class="mb-5 flex items-start gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-800">
                    <x-icon name="close" class="mt-0.5 h-4 w-4 shrink-0 text-red-500" />
                    <p>{{ session('err') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-800">
                    <p class="font-semibold">Periksa kembali isian berikut:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
                <div class="min-w-0">
                    <h1 class="gov-page-title">@yield('judul')</h1>
                    <p class="mt-1 text-[12.5px] text-slate-500">@yield('subjudul', 'Sistem registrasi dan disposisi surat otomatis.')</p>
                </div>
                @hasSection('aksi')
                    <div class="flex flex-wrap items-center gap-2 no-print">@yield('aksi')</div>
                @endif
            </div>

            @yield('isi')
        </main>

        <footer class="border-t border-slate-200 bg-white no-print">
            {{-- Padding disamakan dengan <main> agar tepi kiri/kanan footer lurus. --}}
            <div class="mx-auto flex w-full max-w-[1360px] flex-col gap-1 px-4 py-4 text-[11.5px] text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-2 sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} {{ $identitas->nama }} · {{ $identitas->namaUnit() }}</p>
                <p class="text-slate-400">SiSurat · Sistem Otomatisasi Registrasi Surat</p>
            </div>
        </footer>
    </div>
</div>
</body>
</html>
