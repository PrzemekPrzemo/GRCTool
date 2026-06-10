<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrcAlertNotification extends Notification
{
    /**
     * @param  string        $subject
     * @param  array<string> $bodyLines
     * @param  string|null   $actionUrl
     * @param  string|null   $actionLabel
     */
    public function __construct(
        private readonly string $subject,
        private readonly array $bodyLines,
        private readonly ?string $actionUrl = null,
        private readonly ?string $actionLabel = null,
    ) {}

    /**
     * @param  mixed $notifiable
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject);

        foreach ($this->bodyLines as $line) {
            $message->line($line);
        }

        if ($this->actionUrl !== null && $this->actionLabel !== null) {
            $message->action($this->actionLabel, $this->actionUrl);
        }

        return $message;
    }
}
