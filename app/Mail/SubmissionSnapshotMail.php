<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubmissionSnapshotMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public array $snapshot;
    public string $kecamatanName;

    public function __construct(array $snapshot, string $kecamatanName)
    {
        $this->snapshot = $snapshot;
        $this->kecamatanName = $kecamatanName;
    }

    public function build()
    {
        return $this->subject('Notifikasi: Data Baru Menunggu Verifikasi')
            ->view('emails.submission_snapshot')
            ->with([
                'snapshot' => $this->snapshot,
                'kecamatan' => $this->kecamatanName,
            ]);
    }
}
