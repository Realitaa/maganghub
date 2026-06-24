<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JoinRequestRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $groupId,
        public string $groupName
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
        return [
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'message' => "Permintaan bergabung ke Kelompok Magang {$this->groupName} ditolak. Kamu bisa meminta bergabung dengan kelompok lain atau membuat sendiri. Semangat ya.",
        ];
    }
}
