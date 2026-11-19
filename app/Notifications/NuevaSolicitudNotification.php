<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo para el ADMINISTRADOR cuando un turista pide una asesoría o un vehículo.
 * (Mientras trabajamos en la computadora, el correo se guarda en storage/logs/laravel.log)
 */
class NuevaSolicitudNotification extends Notification
{
    public function __construct(public Solicitud $solicitud) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->solicitud;
        $servicio = Solicitud::SERVICIOS[$s->servicio]['nombre'];

        return (new MailMessage)
            ->subject('Nueva solicitud: '.$servicio)
            ->greeting('¡Hola, '.$notifiable->nombre.'!')
            ->line($s->usuario->nombre.' pidió una «'.$servicio.'».')
            ->line(($s->esVehiculo() ? 'Recogida en: ' : 'Destino: ').$s->lugar.' · Fechas: '.$s->fechasTexto().' · Personas: '.$s->cantidad_personas)
            ->when($s->tipo_vehiculo, fn ($m) => $m->line('Vehículo: '.Solicitud::TIPOS_VEHICULO[$s->tipo_vehiculo]['nombre']))
            ->line('Teléfono: '.$s->telefono)
            ->when($s->mensaje, fn ($m) => $m->line('Mensaje: «'.$s->mensaje.'»'))
            ->action('Ver solicitudes', route('admin.solicitudes.index'))
            ->salutation('Explora RD');
    }
}
