# SiSurat — Alur, Status, dan Hak Akses

Sistem otomatisasi registrasi surat Pemerintah Kabupaten Bogor. Dokumen ini menjelaskan
alur kerja surat masuk dan surat keluar, daftar status, matriks hak akses per peran, serta
catatan operasional (notifikasi, jadwal, ekspor, dan keamanan).

---

## 1. Peran pengguna

Sistem memakai **empat peran** dengan pemisahan tugas tata naskah. Setiap peran fokus pada
satu bagian alur sehingga pekerjaan surat tidak bertumpu pada satu akun.

| Peran (`roles.nama`) | Sebutan | Fokus tugas |
| --- | --- | --- |
| `admin` | Administrator | Pengelolaan teknis dan administrasi sistem: pengguna, unit kerja, klasifikasi, instansi, pengaturan, log aktivitas, pengawasan seluruh surat, dan pemeliharaan data (hapus permanen). Tidak menjalankan pencatatan, disposisi, pengiriman, atau tindak lanjut. |
| `pimpinan` | Pimpinan | Pemeriksaan surat dan pemberian instruksi disposisi: ACC & disposisi surat masuk, ACC/tolak surat keluar, batalkan disposisi. |
| `petugas_tu` | Petugas TU | Pencatatan, pengiriman, dan pengarsipan surat: catat/ubah surat masuk & keluar, unggah lampiran, ajukan, kirim, arsipkan, serta memantau tindak lanjut. |
| `staf` | Staf/Umpeg | Pelaksanaan instruksi disposisi unitnya: ubah status, unggah bukti, nyatakan selesai. |

Migrasi `2026_11_25_000001_kembalikan_peran_petugas_tu_dan_pimpinan` memastikan keempat
peran tersedia. Middleware `role:` mencocokkan peran secara tepat (administrator tidak otomatis
diloloskan): `role:petugas_tu` untuk rute pengelolaan surat, `role:pimpinan` untuk ACC/disposisi,
dan `role:admin` untuk data master, pengaturan, serta hapus permanen. Policy
(`SuratMasukPolicy`, `SuratKeluarPolicy`, `DisposisiPolicy`, `DisposisiTujuanPolicy`)
memeriksa aturan yang sama pada level aksi.

Akun **hanya dibuat administrator** melalui menu *Pengguna*. Pendaftaran publik dihapus.
Akun baru memakai kata sandi sementara dan **wajib menggantinya saat login pertama**
(middleware `WajibGantiSandi`). Penghapusan akun sendiri dinonaktifkan.

Administrator pertama dibuat dari terminal dengan `php artisan sisurat:buat-admin`; seeder tidak
membuat akun apa pun sehingga tidak ada sandi bawaan yang diketahui orang lain.

---

## 2. Alur surat masuk

```
Petugas TU catat + scan  →  [diterima]
      ↓ pimpinan ACC & disposisi (instruksi, batas waktu, unit tujuan)
[didisposisi]  →  staf unit tujuan menindaklanjuti
      ↓ ada tujuan berstatus diproses / selesai
[diproses]
      ↓ semua tujuan disposisi = selesai
[selesai]
      ↓ Petugas TU mencatat lokasi arsip fisik
[diarsipkan]
```

### Status surat masuk

| Status | Arti | Pemicu |
| --- | --- | --- |
| `diterima` | Surat tercatat, belum didisposisi. | Petugas TU menyimpan surat (nomor agenda otomatis `AGD/0001/XI/2026`). |
| `didisposisi` | Disposisi sudah dibuat, semua unit tujuan masih `belum`. | Pimpinan menekan *ACC & Disposisi*. |
| `diproses` | Minimal satu unit tujuan berstatus `diproses` atau `selesai`. | Unit tujuan memperbarui tindak lanjut / mengunggah bukti. |
| `selesai` | Seluruh unit tujuan berstatus `selesai`. | Unit tujuan terakhir menyatakan selesai. |
| `diarsipkan` | Surat selesai dan lokasi arsip fisik dicatat. | Petugas TU menekan *Arsipkan* pada surat berstatus `selesai`. |

Aturan penting: status surat **tidak lagi** menjadi `diproses` bila semua unit tujuan masih
`belum`. Perhitungan dilakukan terpusat di `DisposisiController::hitungStatusSurat()`.

### Tindak lanjut disposisi (`disposisi_tujuan.status_tindak_lanjut`)

| Status | Arti |
| --- | --- |
| `belum` | Unit tujuan belum memulai. |
| `diproses` | Sedang ditindaklanjuti (otomatis saat bukti diunggah pertama kali). |
| `selesai` | Selesai; sistem mencatat `tgl_selesai` dan `selesai_oleh`. |

Status tindak lanjut dan unggahan bukti terbuka untuk pengguna dari **unit tujuan** dan Petugas TU
(`DisposisiTujuanPolicy::ubah`). Petugas TU memantau seluruh unit, sedangkan Staf/Umpeg hanya
unitnya sendiri.

---

## 3. Alur surat keluar

```
Petugas TU buat draf + nomor otomatis  →  [draf]
      ↓ Petugas TU mengajukan
[menunggu_ttd]  → pimpinan memutuskan
      ├─ ACC → [disetujui]
      └─ tolak / minta revisi (+ catatan) → [revisi] → Petugas TU perbaiki → ajukan lagi
[disetujui]
      ↓ Petugas TU unggah berkas final PDF + catat pengiriman (tanggal, penerima, metode, no. bukti)
[terkirim]
      ↓ Petugas TU arsipkan
[diarsipkan]
```

### Status surat keluar

| Status | Arti | Pemicu |
| --- | --- | --- |
| `draf` | Surat dibuat, belum diajukan. | Petugas TU menyimpan surat baru. |
| `menunggu_ttd` | Menunggu keputusan. | Petugas TU menekan *Ajukan*. |
| `revisi` | Dikembalikan dengan catatan. | *Tolak / Minta Revisi* (catatan wajib). |
| `disetujui` | Disetujui & ditandatangani (tercatat `penandatangan_id`, `disetujui_pada`). | *ACC Surat* — **hanya dari** `menunggu_ttd`. |
| `terkirim` | Berkas final tersimpan dan pengiriman tercatat. | Petugas TU menyimpan form pengiriman + PDF final. |
| `diarsipkan` | Arsip dinamis. | Petugas TU menekan *Arsipkan* dari status `terkirim`. |

Validasi alur dijaga `SuratKeluarPolicy` (`ajukan`, `setujui`/`acc`, `tolak`, `kirim`,
`arsipkan`), bukan hanya oleh tampilan tombol. Penomoran memakai kode unit kerja; unit
dengan kode `TU` adalah unit tata usaha yang menerima kartu disposisi hasil ACC.

---

## 4. Relasi surat masuk ↔ surat keluar

`surat_keluar.surat_masuk_id` menyimpan relasi balasan. Dari detail surat masuk, tombol
**Buat Balasan** membuka form surat keluar dengan instansi, perihal, dan isi ringkas yang
sudah terisi otomatis. Daftar balasan tampil pada halaman surat masuk.

---

## 5. Matriks hak akses

Keterangan: ✅ penuh, 🚫 tidak boleh.

| Tindakan | admin | pimpinan | petugas_tu | staf |
| --- | --- | --- | --- | --- |
| Lihat daftar & detail surat masuk (termasuk `rahasia`) | ✅ | ✅ | ✅ | unit terkait |
| Catat / ubah surat masuk | 🚫 | 🚫 | ✅ | 🚫 |
| Hapus surat masuk (soft delete) | 🚫 | 🚫 | ✅ | 🚫 |
| Hapus permanen surat + disposisi, semua status (tidak dapat dibatalkan) | ✅ | 🚫 | 🚫 | 🚫 |
| Unduh lampiran surat | ✅ | ✅ | ✅ | unit terkait |
| Kelola (unggah/hapus) lampiran | 🚫 | 🚫 | ✅ | 🚫 |
| ACC & buat disposisi surat masuk | 🚫 | ✅ | 🚫 | 🚫 |
| Batalkan disposisi | ✅ | ✅ | 🚫 | 🚫 |
| Ubah status tindak lanjut | 🚫 | 🚫 | ✅ semua unit | ✅ unitnya |
| Unggah bukti tindak lanjut | 🚫 | 🚫 | ✅ semua unit | ✅ unitnya |
| Arsipkan surat masuk | 🚫 | 🚫 | ✅ | 🚫 |
| Buat / ubah / hapus draf surat keluar | 🚫 | 🚫 | ✅ | 🚫 |
| Ajukan surat keluar | 🚫 | 🚫 | ✅ | 🚫 |
| Setujui / tolak surat keluar | 🚫 | ✅ | 🚫 | 🚫 |
| Kirim (unggah final + data kirim) | 🚫 | 🚫 | ✅ | 🚫 |
| Arsipkan surat keluar | 🚫 | 🚫 | ✅ | 🚫 |
| Lihat surat keluar (termasuk `rahasia`) | ✅ | ✅ | ✅ | unit terkait |
| Laporan & ekspor | ✅ | ✅ | ✅ | unitnya |
| Kelola pengguna & data master | ✅ | 🚫 | 🚫 | 🚫 |
| Log aktivitas & pengaturan instansi | ✅ | 🚫 | 🚫 | 🚫 |

Catatan `rahasia`: sifat surat tetap tersimpan dan ditandai pada tampilan. Rahasia membatasi
akses **antarunit**, bukan memblokir unit yang bertugas: Administrator, Petugas TU, dan Pimpinan
melihat semua surat rahasia, sedangkan Staf/Umpeg tetap dapat membuka surat rahasia yang
didisposisikan ke unitnya—beserta kartu disposisi dan buktinya—karena tugasnya menindaklanjuti.
Unit lain tidak melihat surat itu. "Unit terkait" berarti unit pembuat surat keluar atau unit
tujuan disposisi. Batas akses administratif (pengguna, data master, log, dan pengaturan
instansi) tetap hanya untuk administrator.

### Aturan hapus

- Surat masuk: **tidak boleh** dihapus bila sudah pernah didisposisi atau sudah `diarsipkan`.
- Surat keluar: hanya boleh dihapus pada status `draf` atau `revisi`.
- Penghapusan memakai **SoftDeletes** (`deleted_at`) untuk `surat_masuk`, `surat_keluar`, dan
  `disposisi`; berkas di disk tidak dihapus agar jejak arsip tetap utuh.
- Pengguna yang memiliki riwayat surat hanya **dinonaktifkan** (`users.aktif = false`).
- **Hapus permanen** dibuka dari tombol *Hapus Permanen Surat Masuk* di bawah detail surat masuk
  (kartu *Zona berbahaya* pada detail surat keluar), lalu
  melewati halaman konfirmasi tersendiri: nomor surat harus diketik persis (nomor agenda untuk
  surat masuk, nomor surat untuk surat keluar) sebelum tombol aktif. Hanya **administrator** yang
  boleh melakukannya, untuk surat apa pun statusnya — termasuk yang sudah didisposisi, diproses,
  atau diarsipkan. Yang ikut terhapus: seluruh disposisi
  dan tujuannya, lampiran beserta berkasnya, kartu disposisi PDF, bukti tindak lanjut, dan berkas
  final surat keluar. Berkas di disk ikut dibuang sehingga tidak dapat dipulihkan; gunakan
  *Hapus* biasa bila hanya ingin memindahkan ke arsip terhapus. Surat keluar yang membalas surat
  masuk tetap ada dan relasinya (`surat_masuk_id`) menjadi kosong. Log aktivitas mencatat
  `surat_masuk.hapus_permanen` / `surat_keluar.hapus_permanen`.

---

## 6. Notifikasi

Notifikasi dikirim lewat kanal **database** (lonceng di topbar, halaman `/notifikasi`) dan
**email**.

| Notifikasi | Penerima | Pemicu |
| --- | --- | --- |
| `DisposisiBaru` | Pengguna unit tujuan | Disposisi disimpan (surat masuk, atau kartu hasil ACC surat keluar). |
| `MenungguTandaTangan` | Pimpinan selain pengaju | Petugas TU mengajukan surat keluar. |
| `MenungguTandaTangan` (mode revisi) | Pembuat surat | Pimpinan menolak / meminta revisi. |
| `StatusTindakLanjut` | Pengguna unit tujuan (termasuk surat rahasia) | Petugas TU memperbarui status tindak lanjut. |
| `PengingatBatasWaktu` | Pengguna unit tujuan (termasuk surat rahasia) | Command `disposisi:ingatkan`. |

Notifikasi pengajuan ditujukan ke pimpinan (`User::scopePemberiDisposisi`) selain akun pengaju,
agar keputusan ACC masuk ke meja pemutus.

Jadwal pengingat ada di `routes/console.php`:

```php
Schedule::command('disposisi:ingatkan')->dailyAt('07:30')->weekdays();
```

Command dapat dijalankan manual (mis. untuk uji):

```sh
php artisan disposisi:ingatkan --hari=2
```

Agar jadwal berjalan di server, cron Laravel scheduler harus aktif:

```
* * * * * cd /path/sisurat && php artisan schedule:run >> /dev/null 2>&1
```

Email memakai `MAIL_MAILER` pada `.env`. Untuk produksi isi SMTP instansi; pada lingkungan
pengembangan `log` cukup (email ditulis ke `storage/logs/laravel.log`).

---

## 7. Laporan & ekspor

Menu **Laporan** (`/laporan`) menyediakan:

- Filter `dari`–`sampai`, `instansi_id`, `klasifikasi_id`, `status`, dan `jenis` (masuk/keluar).
- **Ekspor Excel**: CSV UTF-8 dengan pemisah titik koma (langsung rapi di Excel Indonesia).
- **Ekspor PDF**: dibuat dengan dompdf (`dompdf/dompdf`).
- **Laporan Tindak Lanjut** (`/laporan/tindak-lanjut`): status per unit, batas waktu,
  keterlambatan (merah), catatan, dan tautan bukti.

Semua laporan mematuhi `SuratMasuk::scopeTerlihatOleh()` / `SuratKeluar::scopeTerlihatOleh()`,
sedangkan laporan tindak lanjut memakai `DisposisiTujuan::scopeTerlihatOleh()`.
Untuk peran `admin`, `petugas_tu`, dan `pimpinan` scope tersebut mengembalikan seluruh data;
Staf/Umpeg hanya melihat unitnya sendiri, termasuk surat rahasia yang didisposisikan ke unitnya.
Laporan unit lain tidak memuat baris tersebut.

---

## 8. Keamanan & konfigurasi

- Basis data memakai **MySQL** (`DB_CONNECTION=mysql`), bukan SQLite. `database/database.sqlite`
  tidak lagi disertakan; entri `/database/*.sqlite` masuk `.gitignore`.
- Semua perubahan data memakai `@csrf`; unggahan dibatasi tipe (`pdf,jpg,jpeg,png`) dan ukuran.
- Berkas lampiran, surat final, dan bukti disimpan di disk privat `storage/app` dan hanya bisa
  diunduh lewat rute ber-otorisasi (`lampiran.unduh`, `surat-keluar.file-final`,
  `tujuan.bukti.unduh`) — tidak ada akses langsung via URL publik.
- Kata sandi sementara + `must_change_password` mencegah akun buatan administrator dipakai
  sebelum sandinya diganti.
- Log aktivitas (`log_aktivitas`) mencatat registrasi, disposisi, perubahan status, pengiriman,
  arsip, unggahan, dan penghapusan.

### Dashboard

`DashboardController` menampilkan antrean sesuai peran:

- **Pimpinan**: surat masuk menunggu ACC dan surat keluar menunggu ACC, diurutkan menurut sifat.
- **Petugas TU**: surat yang belum didisposisikan, sudah di-ACC tetapi belum dikirim, dan belum
diarsipkan.
- **Staf/Umpeg**: disposisi unitnya yang belum selesai, lengkap dengan batas waktu.
- **Administrator**: ringkasan akun, log aktivitas terbaru, dan angka registrasi; tanpa antrean
  operasional karena alur surat dijalankan peran lain.

### ACC & kartu disposisi

- **Surat masuk** — pimpinan membuka surat lalu menekan **ACC & Disposisi**
  (`GET/POST /surat-masuk/{id}/acc`). Form terisi otomatis (dari, perihal, nomor, tanggal,
  klasifikasi); pimpinan mengisi indeks, tanggal penyelesaian, instruksi, dan unit tujuan.
  Simpan → disposisi dibuat, status surat menjadi `didisposisi`, PDF kartu diterbitkan.
- **Surat keluar** — dari status `menunggu_ttd`, tombol **ACC Surat** di halaman detail
  (`POST /surat-keluar/{id}/acc`) menetapkan penanda tangan, mengubah status menjadi
  `disetujui`, dan membuat kartu disposisi untuk unit berkode `TU` (instruksi bawaan:
  sampaikan, catat pengiriman, arsipkan).
- **PDF kartu** dibuat otomatis oleh `App\Services\KartuDisposisiService` (dompdf) setiap kali
  disposisi dibuat atau diubah, disimpan privat di `storage/app/kartu-disposisi`.
  Kop memakai identitas dari menu **Pengaturan Instansi** (admin): nama, alamat, telepon,
  situs, dan lambang (lambang dikirim sebagai data URI agar ikut tercetak).
- **Unduh/cetak** — `GET /disposisi/{id}/pdf` untuk satu kartu, `GET /disposisi/cetak/pdf?ids[]=…`
  untuk banyak kartu dalam satu PDF (checkbox di halaman Disposisi), dan pratinjau
  `GET /disposisi/{id}/kartu` dengan tombol Cetak (dialog cetak peramban).
- **Hak akses** — ACC dan disposisi hanya pimpinan; unduh kartu: admin,
  petugas TU, pimpinan, dan unit tujuan (`DisposisiPolicy::unduhKartu`); unit lain mendapat 403.
  Pratinjau kartu (`/disposisi/{id}/kartu`) dan bukti tindak lanjut memakai aturan yang sama,
  sehingga akun staf di unit tujuan juga bisa membukanya. Bila surat induk bertanda
  `rahasia`, akses tetap mengikuti unit tujuan: staf unit tujuan dapat membuka daftar,
  pratinjau kartu, PDF, bukti, dan tindak lanjutnya, sedangkan unit lain tertutup.
- **Notifikasi** — `DisposisiBaru` dikirim ke seluruh pengguna unit tujuan lewat basis data dan
  surel, dengan kartu disposisi PDF terlampir. Surat rahasia memakai penerima yang sama karena
  staf unit tujuan yang menindaklanjutinya; unit lain tidak menerima notifikasi maupun lampiran
  kartunya. Baris surat rahasia hanya muncul di laporan tindak lanjut dan hitungan terbuka dasbor
  bagi unit tujuan serta peran yang melihat semua surat. Notifikasi lama yang penerimanya tidak
  berhak (di luar unit tujuan) dibersihkan oleh migrasi
  `2026_11_20_000001_hapus_notifikasi_rahasia_lama` (`App\Services\PembersihNotifikasiRahasia`),
  termasuk job-nya di tabel `jobs` maupun yang sudah gagal di tabel `failed_jobs`
  (`App\Services\PembersihAntreanRahasia`, dijalankan ulang oleh migrasi
  `2026_11_20_000002_hapus_antrean_rahasia_gagal`). Pembersih memakai
  `bolehDilihatOleh()` sehingga notifikasi staf unit tujuan dipertahankan.
- Kartu disegarkan otomatis saat unit mengubah status tindak lanjut atau mengunggah bukti.

### Ringkasan perintah

```sh
php artisan migrate --seed       # skema + data master (unit, klasifikasi, instansi, peran)
php artisan sisurat:buat-admin   # administrator pertama (interaktif)
php artisan disposisi:ingatkan   # kirim pengingat disposisi
php artisan test                 # seluruh tes fitur
```

### Instalasi awal

1. `php artisan migrate --seed` — membuat tabel dan data master. **Tidak ada akun** yang dibuat.
2. `php artisan sisurat:buat-admin` — menanyakan surel, nama, dan kata sandi administrator
   pertama. Sandi diketik tanpa echo, diulang untuk konfirmasi, dan tunduk pada aturan minimum
   10 karakter dengan huruf serta angka. Untuk otomatisasi, opsi tersedia:

   ```sh
   php artisan sisurat:buat-admin kepala@bogorkab.go.id --nama="Nama Lengkap"
   php artisan sisurat:buat-admin kepala@bogorkab.go.id --nama="Nama Lengkap" --sandi="SandiKuat2026"
   ```

   Opsi `--sandi` kurang aman karena tersimpan di riwayat shell; tanpa opsi itu sandi ditanyakan
   secara tersembunyi.
3. Masuk lewat `/login` dengan akun tersebut, lalu tambahkan akun staf dari menu **Pengguna**.
   Sandi sementara akun staf wajib diganti saat login pertama.

Aturan sandi minimum 10 karakter (huruf dan angka) berlaku untuk pembuatan administrator,
penggantian wajib, reset sandi, dan perubahan dari profil.
