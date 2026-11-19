<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una solicitud de asesoría de viaje o de renta de vehículo.
 * Tabla: solicitudes
 *
 * La hace un turista desde /asesorias o /renta-de-vehiculos
 * y la atiende el equipo de Explora RD desde el panel del administrador.
 *
 * Estados:
 *   pendiente → el turista la envió y todavía nadie la atiende
 *   atendida  → Explora RD ya contactó al turista
 *   cancelada → el turista la canceló o no se pudo atender
 */
#[Fillable(['usuario_id', 'servicio', 'lugar', 'fecha_inicio', 'fecha_fin', 'cantidad_personas',
    'tipo_vehiculo', 'telefono', 'mensaje', 'estado', 'respuesta'])]
class Solicitud extends Model
{
    // Laravel buscaría «solicituds»; le decimos el nombre correcto en español
    protected $table = 'solicitudes';

    public const SERVICIOS = [
        'asesoria' => ['nombre' => 'Asesoría de viaje', 'icono' => 'bi-chat-heart'],
        'vehiculo' => ['nombre' => 'Renta de vehículo', 'icono' => 'bi-car-front'],
    ];

    public const ESTADOS = ['pendiente', 'atendida', 'cancelada'];

    /** Tipos de vehículo que se pueden pedir (los nombres se traducen con __()). */
    public const TIPOS_VEHICULO = [
        'economico' => ['nombre' => 'Económico', 'icono' => 'bi-car-front', 'pasajeros' => 4,
            'detalle' => 'Pequeño y económico en combustible. Ideal para moverse por la ciudad.'],
        'sedan' => ['nombre' => 'Sedán', 'icono' => 'bi-car-front-fill', 'pasajeros' => 5,
            'detalle' => 'Cómodo para carretera, con espacio para el equipaje de una familia pequeña.'],
        'jeepeta' => ['nombre' => 'Jeepeta (SUV)', 'icono' => 'bi-truck-front', 'pasajeros' => 5,
            'detalle' => 'Más alta y fuerte. La mejor opción para montaña, Jarabacoa, Constanza o Pedernales.'],
        'minivan' => ['nombre' => 'Minivan', 'icono' => 'bi-bus-front', 'pasajeros' => 12,
            'detalle' => 'Para grupos grandes y familias que viajan juntas con mucho equipaje.'],
        'chofer' => ['nombre' => 'Con chofer', 'icono' => 'bi-person-badge', 'pasajeros' => 12,
            'detalle' => 'Un chofer local maneja por ti. Perfecto si no quieres manejar en la isla.'],
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    /** El turista que hizo la solicitud. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function esVehiculo(): bool
    {
        return $this->servicio === 'vehiculo';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /** «Asesoría de viaje» o «Renta de vehículo», traducido. */
    public function nombreServicio(): string
    {
        return __(self::SERVICIOS[$this->servicio]['nombre'] ?? $this->servicio);
    }

    public function iconoServicio(): string
    {
        return self::SERVICIOS[$this->servicio]['icono'] ?? 'bi-question-circle';
    }

    /** Nombre del tipo de vehículo, traducido (o null si no aplica). */
    public function nombreVehiculo(): ?string
    {
        return $this->tipo_vehiculo ? __(self::TIPOS_VEHICULO[$this->tipo_vehiculo]['nombre'] ?? $this->tipo_vehiculo) : null;
    }

    /** Fechas en texto: «12/01/2027» o «12/01/2027 – 15/01/2027». */
    public function fechasTexto(): string
    {
        $texto = $this->fecha_inicio->format('d/m/Y');

        return $this->fecha_fin ? $texto.' – '.$this->fecha_fin->format('d/m/Y') : $texto;
    }

    /** El turista puede cancelarla mientras esté pendiente. */
    public function puedeCancelarla(): bool
    {
        return $this->estaPendiente();
    }
}
