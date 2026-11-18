<?php

namespace App\Notifications;

use App\Models\SuratKeluar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Dikirim saat surat diajukan, dan saat surat perlu revisi. */
class MenungguTandaTangan extends Notification
{
    use Queueable;

    public function __construct(public SuratKeluar $surat, public string $mode = 'baru')
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'jenis' => $this->mode === 'revisi' ? 'surat_revisi' : 'menunggu_ttd',
            'judul' => $this->mode === 'revisi'
                ? 'Perlu revisi: ' . $this->surat->no_surat
                : 'Menunggu tanda tangan: ' . $this->surat->no_surat,
            'pesan' => $this->surat->hal,
            'url' => route('surat-keluar.show', $this->surat),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->greeting('Yth. ' . $notifiable->name)
            ->subject(($this->mode === 'revisi' ? 'Perlu revisi: ' : 'Menunggu tanda tangan: ') . $this->surat->no_surat);

        if ($this->mode === 'revisi') {
            $mail->line('Surat berikut dikembalikan untuk direvisi:')
                ->line($this->surat->no_surat . ' — ' . $this->surat->hal)
                ->line('Catatan revisi: ' . ($this->surat->catatan_revisi ?? '-'));
        } else {
            $mail->line('Terdapat surat keluar yang menunggu persetujuan dan tanda tangan Anda:')
                ->line($this->surat->no_surat . ' — ' . $this->surat->hal)
                ->line('Diajukan oleh: ' . ($this->surat->pembuat?->name ?? 'Staf'));
        }

        return $mail->action('Buka SiSurat', route('surat-keluar.show', $this->surat));
    }
}
