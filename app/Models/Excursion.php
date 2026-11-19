<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Una excursión, circuito o paquete que publica una agencia.
 * Tabla: excursiones
 */
#[Fillable([
    'agencia_id', 'destino_id', 'tipo', 'categoria', 'titulo', 'slug', 'descripcion',
    'precio', 'duracion', 'ubicacion', 'foto', 'disponible',
])]
class Excursion extends Model
{
    /** Laravel diría «excursions» (plural en inglés); el nombre correcto es «excursiones». */
    protected $table = 'excursiones';

    /** Tipos de publicación. */
    public const TIPOS = [
        'excursion' => 'Excursión',
        'circuito' => 'Circuito',
        'paquete' => 'Paquete',
    ];

    /** Categorías de experiencias (del documento del proyecto). */
    public const CATEGORIAS = [
        'playas' => ['nombre' => 'Playas', 'icono' => 'bi-umbrella'],
        'montana' => ['nombre' => 'Montaña', 'icono' => 'bi-tree'],
        'catamaran' => ['nombre' => 'Catamarán', 'icono' => 'bi-life-preserver'],
        'cultura' => ['nombre' => 'Cultura', 'icono' => 'bi-bank'],
        'gastronomia' => ['nombre' => 'Gastronomía', 'icono' => 'bi-cup-hot'],
        'familia' => ['nombre' => 'Familia', 'icono' => 'bi-people'],
        'aventura' => ['nombre' => 'Aventura', 'icono' => 'bi-lightning-charge'],
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'disponible' => 'boolean',
        ];
    }

    /** Antes de guardar, crea el slug a partir del título si no tiene. */
    protected static function booted(): void
    {
        static::saving(function (Excursion $excursion) {
            if (empty($excursion->slug)) {
                // Si otra excursión ya usa ese slug, le agrega -2, -3... para que no se repita
                $base = Str::slug($excursion->titulo) ?: 'excursion';
                $slug = $base;
                $n = 2;
                while (static::where('slug', $slug)->where('id', '!=', $excursion->id ?? 0)->exists()) {
                    $slug = $base.'-'.$n++;
                }
                $excursion->slug = $slug;
            }
        });
    }

    /** Las direcciones usan el slug: /excursiones/catamaran-a-isla-saona */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Solo las excursiones que el público puede ver:
     * marcadas como disponibles y de una agencia que no esté suspendida.
     * Uso: Excursion::publicas()->get()
     */
    public function scopePublicas($consulta)
    {
        return $consulta->where('disponible', true)
            ->whereHas('agencia', fn ($a) => $a->where('activo', true));
    }

    /**
     * Agrega el promedio de estrellas y la cantidad de reseñas visibles
     * (en una sola consulta, para no preguntar a la base de datos tarjeta por tarjeta).
     * Uso: Excursion::publicas()->conValoracion()->get()
     */
    public function scopeConValoracion($consulta)
    {
        return $consulta
            ->withAvg(['resenas as promedio_estrellas' => fn ($r) => $r->where('visible', true)], 'estrellas')
            ->withCount(['resenas as total_resenas' => fn ($r) => $r->where('visible', true)]);
    }

    /** Promedio de estrellas (ej.: 4.5), o null si todavía no tiene reseñas. */
    public function promedioEstrellas(): ?float
    {
        $promedio = array_key_exists('promedio_estrellas', $this->attributes)
            ? $this->attributes['promedio_estrellas']
            : $this->resenas()->visibles()->avg('estrellas');

        return $promedio === null ? null : round((float) $promedio, 1);
    }

    /** Cantidad de reseñas visibles. */
    public function totalResenas(): int
    {
        return (int) (array_key_exists('total_resenas', $this->attributes)
            ? $this->attributes['total_resenas']
            : $this->resenas()->visibles()->count());
    }

    /**
     * ¿Este usuario puede dejar una reseña?
     * Debe ser turista y tener una reserva CONFIRMADA de esta excursión cuya fecha ya pasó.
     */
    public function puedeResenar(?Usuario $usuario): bool
    {
        return $usuario !== null && $usuario->esTurista()
            && $this->reservas()
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'confirmada')
                ->whereDate('fecha', '<', today())
                ->exists();
    }

    /** ¿Esta excursión se puede mostrar y reservar? */
    public function esPublica(): bool
    {
        return $this->disponible && (bool) $this->agencia?->activo;
    }

    /** La agencia (usuario con rol «agencia») que la publica. */
    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'agencia_id');
    }

    /** El destino donde se hace. */
    public function destino(): BelongsTo
    {
        return $this->belongsTo(Destino::class, 'destino_id');
    }

    /** Una excursión recibe muchas reservas (1:N). */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'excursion_id');
    }

    /** Turistas que guardaron esta excursión en favoritos (muchos a muchos). */
    public function guardadaPor(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'favoritos', 'excursion_id', 'usuario_id')->withTimestamps();
    }

    /** Una excursión recibe muchas reseñas (1:N). */
    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'excursion_id');
    }

    /** Nombre del tipo: «Excursión», «Circuito» o «Paquete». */
    public function nombreTipo(): string
    {
        return __(self::TIPOS[$this->tipo] ?? 'Excursión'); // __() lo traduce al idioma elegido
    }

    /** Nombre de la categoría, o null si no tiene. */
    public function nombreCategoria(): ?string
    {
        $nombre = self::CATEGORIAS[$this->categoria]['nombre'] ?? null;

        return $nombre ? __($nombre) : null;
    }

    /** Precio con formato: «RD$ 3,500». */
    public function precioFormateado(): string
    {
        return 'RD$ '.number_format((float) $this->precio, 0, '.', ',');
    }

    /** Número de WhatsApp de la agencia (o el general de Explora RD). */
    public function whatsappAgencia(): string
    {
        return $this->agencia?->whatsapp ?: config('explora.whatsapp');
    }

    /** Enlace de WhatsApp con un mensaje ya escrito sobre esta excursión. */
    public function enlaceWhatsapp(?string $fecha = null, ?int $personas = null): string
    {
        $mensaje = __('¡Hola! Vi en Explora RD «:titulo» y quisiera más información.', ['titulo' => $this->titulo]);
        if ($fecha) {
            $mensaje .= ' '.__('Fecha').': '.$fecha.'.';
        }
        if ($personas) {
            $mensaje .= ' '.__('Personas').': '.$personas.'.';
        }

        return 'https://wa.me/'.$this->whatsappAgencia().'?text='.rawurlencode($mensaje);
    }
}
