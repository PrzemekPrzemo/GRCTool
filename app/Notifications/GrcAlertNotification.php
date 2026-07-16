<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrcAlertNotification extends Notification
{
    /**
     * @param  array<string>  $bodyLines
     */
    public function __construct(
        private readonly string $subject,
        private readonly array $bodyLines,
        private readonly ?string $actionUrl = null,
        private readonly ?string $actionLabel = null,
    ) {}

    /**
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
