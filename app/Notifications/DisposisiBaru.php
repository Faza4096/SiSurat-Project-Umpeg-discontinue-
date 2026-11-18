<?php

namespace App\Notifications;

use App\Models\Disposisi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/** Dikirim ke pengguna unit tujuan saat disposisi dibuat. */
class DisposisiBaru extends Notification
{
    use Queueable;

    public function __construct(public Disposisi $disposisi)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'jenis' => 'disposisi_baru',
            'judul' => 'Disposisi baru: ' . $this->disposisi->nomorSurat(),
            'pesan' => $this->disposisi->perihal() . ' — batas '
                . ($this->disposisi->tgl_penyelesaian?->translatedFormat('d F Y') ?? 'tanpa batas'),
            'url' => $this->tautan(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Disposisi baru: ' . $this->disposisi->nomorSurat())
            ->greeting('Yth. ' . $notifiable->name)
            ->line('Anda menerima disposisi baru dari ' . ($this->disposisi->pembuat?->name ?? 'operator') . '.')
            ->line('Nomor: ' . $this->disposisi->nomorSurat() . ' — ' . $this->disposisi->perihal())
            ->line('Dari: ' . $this->disposisi->asal())
            ->line('Instruksi: ' . $this->disposisi->instruksi)
            ->line('Batas penyelesaian: ' . ($this->disposisi->tgl_penyelesaian?->translatedFormat('d F Y') ?? 'tidak ditentukan'))
            ->action('Buka SiSurat', $this->tautan())
            ->line('Kartu disposisi terlampir pada surel ini. Mohon ditindaklanjuti dan unggah bukti hasilnya.');

        $path = $this->disposisi->kartu_path;

        if ($path && Storage::exists($path)) {
            $mail->attach(Storage::path($path), [
                'as' => $this->disposisi->kartu_nama ?: 'kartu-disposisi.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    private function tautan(): string
    {
        return $this->disposisi->surat_masuk_id
            ? route('surat-masuk.show', $this->disposisi->surat_masuk_id)
            : route('surat-keluar.show', $this->disposisi->surat_keluar_id);
    }
}
