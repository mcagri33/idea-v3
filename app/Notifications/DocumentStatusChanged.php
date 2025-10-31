<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentStatusChanged extends Notification
{
    use Queueable;
    private $document;

    /**
     * Create a new notification instance.
     */
    public function __construct($document)
    {
      $this->document = $document;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
      return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
      return (new MailMessage)
        ->line('Bir evrakın durumu güncellendi.')
        ->action($this->document->document_name, url('/documents/' . $this->document->id))
        ->line('Yeni durum: ' . ($this->document->status == 1 ? 'Onaylandı' : 'Reddedildi'))
        ->line($this->document->rejection_note ? 'Red Notu: ' . $this->document->rejection_note : '');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
          'document_id' => $this->document->id,
          'document_name' => $this->document->document_name,
          'status' => $this->document->status == 1 ? 'Onaylandı' : 'Reddedildi',
          'rejection_note' => $this->document->rejection_note,
        ];
    }
}
