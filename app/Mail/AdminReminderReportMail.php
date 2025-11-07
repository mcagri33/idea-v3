<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminReminderReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array<int, array{user:\App\Models\User, missing_count:int, categories: \Illuminate\Support\Collection}> */
    public array $remindersSent;

    public int $year;

    public function __construct(array $remindersSent, int $year)
    {
        $this->remindersSent = $remindersSent;
        $this->year = $year;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Belge Hatırlatma Raporu - {$this->year} Yılı",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_reminder_report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
