<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una reserva que un turista hace de una excursión.
 * Tabla: reservas
 *
 * Estados (diagrama de flujo del Paso 5):
 *   pendiente  → el turista la solicitó y la agencia todavía no responde
 *   confirmada → la agencia tiene disponibilidad y la aceptó
 *   cancelada  → la agencia la rechazó o el turista la canceló
 */
#[Fillable(['usuario_id', 'excursion_id', 'fecha', 'cantidad_personas', 'estado', 'comentario'])]
class Reserva extends Model
{
    public const ESTADOS = ['pendiente', 'confirmada', 'cancelada'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /** El turista que reserva. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /** La excursión reservada. */
    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class, 'excursion_id');
    }

    /** Total estimado: precio por persona × cantidad de personas. */
    public function total(): float
    {
        return (float) $this->excursion->precio * $this->cantidad_personas;
    }

    public function totalFormateado(): string
    {
        return 'RD$ '.number_format($this->total(), 0, '.', ',');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /** ¿La fecha de la excursión ya pasó? */
    public function yaPaso(): bool
    {
        return $this->fecha->lt(today());
    }

    /** El turista puede cancelarla si no está cancelada y la fecha todavía no llega. */
    public function puedeCancelarla(): bool
    {
        return $this->estado !== 'cancelada' && $this->fecha->gt(today());
    }

    /** ¿Esta reserva es de una excursión de esta agencia? */
    public function esDeAgencia(Usuario $agencia): bool
    {
        return (int) $this->excursion->agencia_id === (int) $agencia->id;
    }
}
