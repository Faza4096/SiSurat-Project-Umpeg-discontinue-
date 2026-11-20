<?php

namespace App\Notifications;

use App\Models\DisposisiTujuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Dikirim ke unit tujuan saat operator mengubah status tindak lanjut. */
class StatusTindakLanjut extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DisposisiTujuan $tujuan,
        public string $status,
        public string $oleh,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $disposisi = $this->tujuan->disposisi;

        return [
            'jenis' => 'status_tindak_lanjut',
            'judul' => 'Status tindak lanjut diperbarui: ' . $disposisi->nomorSurat(),
            'pesan' => $disposisi->perihal() . ' — menjadi ' . $this->label() . ' oleh ' . $this->oleh,
            'url' => $disposisi->tautanSurat(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $disposisi = $this->tujuan->disposisi;

        return (new MailMessage)
            ->subject('Status tindak lanjut diperbarui: ' . $disposisi->nomorSurat())
            ->greeting('Yth. ' . $notifiable->name)
            ->line('Status tindak lanjut unit Anda diubah oleh ' . $this->oleh . '.')
            ->line('Nomor: ' . $disposisi->nomorSurat() . ' — ' . $disposisi->perihal())
            ->line('Status baru: ' . $this->label())
            ->action('Buka SiSurat', $disposisi->tautanSurat())
            ->line('Mohon periksa berkas dan bukti tindak lanjut unit Anda.');
    }

    private function label(): string
    {
        return DisposisiTujuan::STATUS[$this->status] ?? $this->status;
    }
}
