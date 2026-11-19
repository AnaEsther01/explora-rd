<?php

namespace App\Notifications;

use App\Models\Reserva;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo para el TURISTA cuando la agencia confirma o rechaza su reserva
 * («Confirma la reserva y avisa al turista» / «Rechaza la reserva y avisa al turista»).
 */
class EstadoReservaNotification extends Notification
{
    public function __construct(public Reserva $reserva) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->reserva;
        $agencia = $r->excursion->agencia;
        $confirmada = $r->estado === 'confirmada';

        $correo = (new MailMessage)
            ->subject(($confirmada ? '¡Reserva confirmada! ' : 'Reserva no disponible: ').$r->excursion->titulo)
            ->greeting('¡Hola, '.$notifiable->nombre.'!');

        if ($confirmada) {
            $correo->line($agencia->nombreVisible().' confirmó tu reserva de «'.$r->excursion->titulo.'».')
                ->line('Fecha: '.$r->fecha->format('d/m/Y').' · Personas: '.$r->cantidad_personas.' · Total estimado: '.$r->totalFormateado())
                ->line('La agencia te contactará con el punto de encuentro y la hora. También puedes escribirle por WhatsApp.');
        } else {
            $correo->line('Lo sentimos: '.$agencia->nombreVisible().' no tiene disponibilidad para «'.$r->excursion->titulo.'» el '.$r->fecha->format('d/m/Y').'.')
                ->line('Puedes elegir otra fecha o buscar otra experiencia en Explora RD.');
        }

        return $correo->action('Ver mis reservas', route('turista.panel'))
            ->salutation('Equipo de Explora RD');
    }
}
