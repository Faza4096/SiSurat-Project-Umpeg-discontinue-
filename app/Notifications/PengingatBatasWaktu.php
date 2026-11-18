<?php

namespace App\Notifications;

use App\Models\DisposisiTujuan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Pengingat batas waktu tindak lanjut disposisi. */
class PengingatBatasWaktu extends Notification
{
    use Queueable;

    public function __construct(public DisposisiTujuan $tujuan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $d = $this->tujuan->disposisi;
        $lewat = $d->tgl_penyelesaian?->isPast();

        return [
            'jenis' => 'pengingat_disposisi',
            'judul' => ($lewat ? 'Lewat batas: ' : 'Mendekati batas: ') . $d->suratMasuk?->no_agenda,
            'pesan' => ($d->suratMasuk?->perihal ?? '-') . ' — batas '
                . ($d->tgl_penyelesaian?->translatedFormat('d F Y') ?? '-'),
            'url' => route('surat-masuk.show', $d->surat_masuk_id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $d = $this->tujuan->disposisi;

        return (new MailMessage)
            ->subject('Pengingat disposisi: ' . ($d->suratMasuk?->no_agenda ?? '-'))
            ->greeting('Yth. ' . $notifiable->name)
            ->line('Disposisi berikut belum dinyatakan selesai:')
            ->line($d->suratMasuk?->no_agenda . ' — ' . $d->suratMasuk?->perihal)
            ->line('Batas penyelesaian: ' . ($d->tgl_penyelesaian?->translatedFormat('d F Y') ?? '-'))
            ->line('Instruksi: ' . $d->instruksi)
            ->action('Buka SiSurat', route('surat-masuk.show', $d->surat_masuk_id));
    }
}
