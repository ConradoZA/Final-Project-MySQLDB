<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequest extends Notification
{
    use Queueable;
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/api/password/find/' . $this->token);
        return (new MailMessage)
            ->greeting('¡Hola!')
            ->line('Recibes este correo porque hemos recibido una petición de recuperación de contraseña.')
            ->action('Resetear Password', url($url))
            ->line('Si no solicitaste resetear tu password, no es encesario que hagas nada.');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
