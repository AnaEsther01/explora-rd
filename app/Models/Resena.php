<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * La reseña (opinión) de un turista sobre una excursión que ya hizo.
 * Tabla: resenas
 *
 * Solo puede escribirla quien tiene una reserva CONFIRMADA de esa excursión
 * cuya fecha ya pasó. Cada turista deja una sola reseña por excursión.
 */
#[Fillable(['usuario_id', 'excursion_id', 'estrellas', 'comentario', 'visible'])]
class Resena extends Model
{
    // Laravel buscaría «resenas» igual, pero lo dejamos escrito para que quede claro
    protected $table = 'resenas';

    protected function casts(): array
    {
        return [
            'estrellas' => 'integer',
            'visible' => 'boolean',
        ];
    }

    /** El turista que escribió la reseña. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /** La excursión reseñada. */
    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class, 'excursion_id');
    }

    /** Solo las reseñas que el administrador no ha ocultado. */
    public function scopeVisibles($consulta)
    {
        return $consulta->where('visible', true);
    }

    /** Nombre corto para mostrar: «María G.» (no mostramos el apellido completo). */
    public function nombreAutor(): string
    {
        $partes = preg_split('/\s+/', trim($this->usuario->nombre ?? ''));
        $nombre = $partes[0] ?? '';

        return isset($partes[1]) ? $nombre.' '.mb_substr($partes[1], 0, 1).'.' : $nombre;
    }
}
