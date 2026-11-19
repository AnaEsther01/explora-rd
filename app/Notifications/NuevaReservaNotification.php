<?php

namespace App\Notifications;

use App\Models\Reserva;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo para la AGENCIA cuando un turista solicita una reserva.
 * (Mientras trabajamos en la computadora, el correo se guarda en storage/logs/laravel.log)
 */
class NuevaReservaNotification extends Notification
{
    public function __construct(public Reserva $reserva) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->reserva;

        return (new MailMessage)
            ->subject('Nueva solicitud de reserva: '.$r->excursion->titulo)
            ->greeting('¡Hola, '.$notifiable->nombreVisible().'!')
            ->line($r->usuario->nombre.' quiere reservar «'.$r->excursion->titulo.'».')
            ->line('Fecha: '.$r->fecha->format('d/m/Y').' · Personas: '.$r->cantidad_personas.' · Total estimado: '.$r->totalFormateado())
            ->when($r->comentario, fn ($m) => $m->line('Comentario: «'.$r->comentario.'»'))
            ->action('Ver la reserva', route('agencia.reservas.show', $r))
            ->line('Confírmala o recházala desde tu panel para avisarle al turista.')
            ->salutation('Equipo de Explora RD');
    }
}
