<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GroupDisbandedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $groupName,
        public string $actorName = 'Administrator',
        public ?string $reason = null
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
        $message = "Kelompok Magang {$this->groupName} telah dibubarkan oleh {$this->actorName}.";
        if ($this->reason) {
            $message .= " Alasan: {$this->reason}";
        }

        return [
            'group_name' => $this->groupName,
            'actor' => $this->actorName,
            'reason' => $this->reason,
            'message' => $message,
        ];
    }
}
