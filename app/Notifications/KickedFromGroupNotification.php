<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KickedFromGroupNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $groupName,
        public string $leaderName,
        public ?string $reason = null,
        public string $actor = 'ketua kelompok'
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
        $message = "Anda telah dikeluarkan dari Kelompok Magang {$this->groupName} oleh {$this->actor} {$this->leaderName}.";
        if ($this->reason) {
            $message .= " Alasan: {$this->reason}";
        }

        return [
            'group_name' => $this->groupName,
            'leader_name' => $this->leaderName,
            'reason' => $this->reason,
            'message' => $message,
        ];
    }
}
