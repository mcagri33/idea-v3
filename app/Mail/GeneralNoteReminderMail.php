<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GeneralNoteReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $year;
    public $subjectText;
    public $messageText;

    public function __construct(User $user, $year, $subject, $message)
    {
        $this->user = $user;
        $this->year = $year;
        $this->subjectText = $subject;
        $this->messageText = $message;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
                    ->view('emails.general_note_reminder')
                    ->with([
                        'user' => $this->user,
                        'year' => $this->year,
                        'messageText' => $this->messageText,
                    ]);
    }
}