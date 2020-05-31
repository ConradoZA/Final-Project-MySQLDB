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
            ->subject('Contraseña recuperada con éxito')
            ->greeting('¡Felicidades!')
            ->line('Has cambiado el password con éxito.')
            ->salutation('Saludos,');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
