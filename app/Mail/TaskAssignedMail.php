<?php

namespace App\Mail;

use App\Models\User;
use App\Models\DocumentAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $user;

    public function __construct(User $user, DocumentAssignment $assignment)
    {
        $this->user = $user;
        $this->assignment = $assignment;
    }

    public function build()
    {
        return $this->subject('Yeni Evrak Göreviniz Var')
                    ->view('emails.task_assigned');
    }
}
