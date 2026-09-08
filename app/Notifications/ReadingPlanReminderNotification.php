<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ReadingPlan $readingPlan,
        public string $timing
    ){
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title;

        $body = match ($this->timing){
            'three_days_before' => "「{$bookTitle}」の期日まであと3日です",
            'on_due_date' => "「{$bookTitle}」の期日です",
            'three_days_after' => "「{$bookTitle}」の期日を3日過ぎています",
            default => "「{$bookTitle}」の読書計画のお知らせです",
        };

        return [
            'title' => '読書計画のお知らせ',
            'body' => $body,
            'timing' => $this->timing,
        ];
    }

}

