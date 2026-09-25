<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base for every in-app notification. Stored in the database (bell icon) and,
 * when enabled in Settings, also sent by email. Queued when a queue is configured.
 */
abstract class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract protected function title(): string;

    abstract protected function message(): string;

    abstract protected function url(object $notifiable): ?string;

    protected function icon(): string
    {
        return 'bi-bell';
    }

    protected function color(): string
    {
        return 'primary';
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (setting('notify_email_enabled', '1') === '1' && ! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'message' => $this->message(),
            'url' => $this->url($notifiable),
            'icon' => $this->icon(),
            'color' => $this->color(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title().' – '.setting('company_name', config('app.name')))
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message());

        if ($url = $this->url($notifiable)) {
            $mail->action('View details', $url);
        }

        return $mail->line('Thank you for choosing '.setting('company_name', config('app.name')).'.');
    }

    /** Portal link for customers, admin link for back-office users. */
    protected function applicationUrl(object $notifiable, string $applicationNo): string
    {
        return $notifiable->isCustomer()
            ? route('portal.applications.show', $applicationNo)
            : route('admin.applications.show', $applicationNo);
    }
}
