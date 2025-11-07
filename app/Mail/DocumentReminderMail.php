<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $customer;

    /** @var \Illuminate\Support\Collection<int, \App\Models\DocumentCategory> */
    public Collection $missingCategories;

    public int $year;

    public function __construct($customer, Collection $missingCategories, int $year)
    {
        $this->customer = $customer;
        $this->missingCategories = $missingCategories;
        $this->year = $year;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->year} Yılı Eksik Belge Hatırlatması - " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document_reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
