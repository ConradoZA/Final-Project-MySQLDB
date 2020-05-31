<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequest extends Notification
{
    use Queueable;
    protected $token;
    public $FRONT_URI;

    public function __construct($token)
    {
        $this->token = $token;
        $this->FRONT_URI = env('FRONT_URI', 'http://localhost:3000');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = $this->FRONT_URI . '/recover/' . $this->token;
        return (new MailMessage)
            ->subject('Recuperación de contraseña desde Play2Games')
            ->greeting('¡Hola!')
            ->line('Recibes este correo porque hemos recibido una petición de recuperación de contraseña.')
            ->action('Resetear Password', url($url))
            ->line('Si no solicitaste resetear tu password, no es encesario que hagas nada.')
            ->salutation('Saludos,');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
