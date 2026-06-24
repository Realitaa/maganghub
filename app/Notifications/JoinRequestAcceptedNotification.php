<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JoinRequestAcceptedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $groupId,
        public string $groupName,
        public string $leaderName
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
            'leader_name' => $this->leaderName,
            'message' => "Permintaan bergabung ke Kelompok Magang {$this->groupName} diterima. Selamat datang, sapa ketua kelompok magangmu, {$this->leaderName}.",
        ];
    }
}
