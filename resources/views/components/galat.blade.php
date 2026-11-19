@props(['kode', 'judul', 'pesan'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kode }} — SiSurat</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-slate-100 text-slate-700">
    <main class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4">
        <div class="gov-card p-8">
            <p class="font-mono text-sm font-bold text-gov-700">{{ $kode }}</p>
            <h1 class="mt-2 text-xl font-bold text-slate-800">{{ $judul }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $pesan }}</p>
            <div class="mt-5">
                <a href="{{ url('/') }}" class="gov-btn">Kembali ke beranda</a>
            </div>
        </div>
    </main>
</body>
</html>
