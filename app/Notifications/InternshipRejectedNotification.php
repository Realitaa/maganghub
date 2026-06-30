<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternshipRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $companyName,
        public ?string $rejectionNote = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = "Permohonan magang Anda di {$this->companyName} telah ditolak.";
        if ($this->rejectionNote) {
            $message .= " Alasan: {$this->rejectionNote}";
        }

        return [
            'company_name' => $this->companyName,
            'rejection_note' => $this->rejectionNote,
            'message' => $message,
        ];
    }
}
