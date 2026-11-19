<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Un destino turístico (Jarabacoa, Punta Cana...).
 * Tabla: destinos
 */
#[Fillable(['nombre', 'slug', 'provincia', 'descripcion', 'actividades', 'traducciones', 'tipo', 'foto', 'latitud', 'longitud'])]
class Destino extends Model
{
    /** Nombres e iconos de cada tipo de destino (el nombre se traduce con __()). */
    public const TIPOS = [
        'playa' => ['nombre' => 'Playa', 'icono' => 'bi-water'],
        'montaña' => ['nombre' => 'Montaña', 'icono' => 'bi-tree'],
        'ciudad' => ['nombre' => 'Ciudad', 'icono' => 'bi-buildings'],
    ];

    /** Idiomas a los que se puede traducir el contenido de un destino. */
    public const IDIOMAS_TRADUCCION = ['en' => 'Inglés', 'fr' => 'Francés'];

    protected function casts(): array
    {
        return [
            'actividades' => 'array',  // se guarda como lista JSON
            'traducciones' => 'array', // {"en": {...}, "fr": {...}}
            'latitud' => 'float',
            'longitud' => 'float',
        ];
    }

    /** ¿Tiene ubicación para mostrarse en el mapa? */
    public function tieneUbicacion(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    /**
     * Datos de este destino para dibujarlo en el mapa (public/js/mapa.js).
     * Solo lo necesario, ya traducido al idioma elegido.
     */
    public function datosMapa(): array
    {
        return [
            'nombre' => $this->nombre,
            'provincia' => $this->provincia,
            'tipo' => $this->tipo,
            'tipoNombre' => __(self::TIPOS[$this->tipo]['nombre'] ?? $this->tipo),
            'lat' => $this->latitud,
            'lng' => $this->longitud,
            'url' => route('destinos.show', $this),
            'foto' => $this->foto && file_exists(public_path($this->foto)) ? asset($this->foto) : null,
        ];
    }

    /** Enlace para pedir indicaciones de cómo llegar (Google Maps, en el celular abre la app). */
    public function enlaceComoLlegar(): ?string
    {
        return $this->tieneUbicacion()
            ? 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitud.','.$this->longitud
            : null;
    }

    /** Antes de guardar, crea el slug a partir del nombre si no tiene. */
    protected static function booted(): void
    {
        static::saving(function (Destino $destino) {
            if (empty($destino->slug)) {
                // Si otro destino ya usa ese slug, le agrega -2, -3... para que no se repita
                $base = Str::slug($destino->nombre) ?: 'destino';
                $slug = $base;
                $n = 2;
                while (static::where('slug', $slug)->where('id', '!=', $destino->id ?? 0)->exists()) {
                    $slug = $base.'-'.$n++;
                }
                $destino->slug = $slug;
            }
        });
    }

    /** Las direcciones usan el slug: /destinos/jarabacoa */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Un destino tiene muchas excursiones (1:N). */
    public function excursiones(): HasMany
    {
        return $this->hasMany(Excursion::class, 'destino_id');
    }

    // ---------- Contenido en el idioma elegido ----------

    /** Descripción en el idioma de la página (si no está traducida, en español). */
    public function descripcionTraducida(): string
    {
        $idioma = app()->getLocale();

        return $this->traducciones[$idioma]['descripcion'] ?? $this->descripcion;
    }

    /** Actividades en el idioma de la página (si no están traducidas, en español). */
    public function actividadesTraducidas(): array
    {
        $idioma = app()->getLocale();

        return $this->traducciones[$idioma]['actividades'] ?? ($this->actividades ?? []);
    }

    /** Primera oración de la descripción (para las tarjetas), en el idioma de la página. */
    public function resumen(): string
    {
        return Str::before($this->descripcionTraducida(), '.').'.';
    }
}
