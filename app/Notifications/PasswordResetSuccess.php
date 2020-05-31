<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetSuccess extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->greeting('¡Felicidades!')
        ->line('Has cambiado el password con éxito.');
        // ->line('Si ya has cambiado el password, no necesitas hacer nada más.')
        // ->line('Si todavía no has cambiado el password, hazlo y protege tu cuenta.');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
