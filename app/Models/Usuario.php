<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Un usuario de Explora RD. Según su «rol» puede ser turista, agencia o administrador.
 * Tabla: usuarios
 */
#[Fillable([
    'nombre', 'correo', 'contrasena', 'telefono', 'rol',
    'nombre_comercial', 'logo', 'descripcion', 'whatsapp', 'direccion', 'fotos', 'videos', 'activo',
])]
#[Hidden(['contrasena', 'remember_token'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    /** Laravel busca la contraseña en «password»; aquí le decimos que se llama «contrasena». */
    protected $authPasswordName = 'contrasena';

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed', // se cifra sola al guardarla
            'fotos' => 'array',       // lista de fotos de la agencia
            'videos' => 'array',      // lista de enlaces de videos
            'activo' => 'boolean',    // false = cuenta suspendida por el administrador
        ];
    }

    /** Solo las agencias que se pueden mostrar al público (rol agencia y no suspendidas). */
    public function scopeAgenciasPublicas($consulta)
    {
        return $consulta->where('rol', 'agencia')->where('activo', true);
    }

    // ---------- Ayudas para el perfil de la agencia ----------

    /** Iniciales para cuando la agencia no tiene logo (ej.: «Brisa Tropical» → «BT»). */
    public function iniciales(): string
    {
        return collect(explode(' ', $this->nombreVisible()))
            ->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->implode('');
    }

    /** ¿Tiene un logo guardado que exista de verdad? */
    public function tieneLogo(): bool
    {
        return $this->logo && file_exists(public_path($this->logo));
    }

    /** Fotos de la agencia que existen de verdad en el disco. */
    public function fotosExistentes(): array
    {
        return array_values(array_filter($this->fotos ?? [], fn ($f) => file_exists(public_path($f))));
    }

    /**
     * Convierte los enlaces de videos en enlaces para mostrar dentro de la página.
     * Acepta YouTube (youtube.com/watch?v=..., youtu.be/...) y Vimeo (vimeo.com/123).
     */
    public function videosParaMostrar(): array
    {
        return collect($this->videos ?? [])->map(function (string $url) {
            if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
                return 'https://www.youtube-nocookie.com/embed/'.$m[1];
            }
            if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
                return 'https://player.vimeo.com/video/'.$m[1];
            }

            return null;
        })->filter()->values()->all();
    }

    /** Enlace de WhatsApp con un mensaje para la agencia. */
    public function enlaceWhatsapp(?string $mensaje = null): string
    {
        $mensaje ??= __('¡Hola! Vi su perfil en Explora RD y quisiera información.');

        return 'https://wa.me/'.($this->whatsapp ?: config('explora.whatsapp')).'?text='.rawurlencode($mensaje);
    }

    // ---------- Relaciones ----------

    /** Una agencia publica muchas excursiones (1:N). */
    public function excursiones(): HasMany
    {
        return $this->hasMany(Excursion::class, 'agencia_id');
    }

    /** Un turista hace muchas reservas (1:N). */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    /**
     * Excursiones que el turista guardó con el corazón ♥ (muchos a muchos, tabla «favoritos»).
     * Las más recientes primero.
     */
    public function favoritas(): BelongsToMany
    {
        return $this->belongsToMany(Excursion::class, 'favoritos', 'usuario_id', 'excursion_id')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    /** Lista de ids de sus excursiones favoritas (se guarda en memoria para no consultar en cada tarjeta). */
    public function idsFavoritas(): array
    {
        return $this->idsFavoritasEnMemoria ??= $this->esTurista()
            ? $this->favoritas()->pluck('excursiones.id')->all()
            : [];
    }

    /** Se borra la memoria cuando cambia la lista de favoritos. */
    public function olvidarFavoritas(): void
    {
        $this->idsFavoritasEnMemoria = null;
    }

    private ?array $idsFavoritasEnMemoria = null;

    /** Un turista escribe muchas reseñas (1:N), una por cada excursión que hizo. */
    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class, 'usuario_id');
    }

    /** Un turista hace muchas solicitudes de asesoría o de renta de vehículo (1:N). */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'usuario_id');
    }

    // ---------- Ayudas para revisar el rol ----------

    public function esTurista(): bool
    {
        return $this->rol === 'turista';
    }

    public function esAgencia(): bool
    {
        return $this->rol === 'agencia';
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    /** Nombre de la ruta del panel que le corresponde según su rol. */
    public function rutaPanel(): string
    {
        return match ($this->rol) {
            'agencia' => 'agencia.panel',
            'administrador' => 'admin.panel',
            default => 'turista.panel',
        };
    }

    /** Nombre que se muestra: el comercial si es agencia; si no, el nombre de la persona. */
    public function nombreVisible(): string
    {
        return $this->esAgencia() && $this->nombre_comercial ? $this->nombre_comercial : $this->nombre;
    }

    // ---------- El correo se llama «correo», no «email» ----------

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }
}
