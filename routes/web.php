<?php

use App\Http\Controllers\Admin\DestinoAdminController;
use App\Http\Controllers\Admin\ExcursionAdminController;
use App\Http\Controllers\Admin\ReporteAdminController;
use App\Http\Controllers\Admin\ResenaAdminController;
use App\Http\Controllers\Admin\SolicitudAdminController;
use App\Http\Controllers\AsistenteController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\GaleriaController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\Agencia\ExcursionAgenciaController;
use App\Http\Controllers\Agencia\PerfilAgenciaController;
use App\Http\Controllers\Agencia\ReservaAgenciaController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EstablecerIdioma;
use App\Http\Controllers\AgenciaController;
use App\Http\Controllers\Auth\ContrasenaController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\ExcursionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\Panel\AdminPanelController;
use App\Http\Controllers\Panel\AgenciaPanelController;
use App\Http\Controllers\Panel\TuristaPanelController;
use Illuminate\Support\Facades\Route;

// Página principal de Explora RD
Route::get('/', [InicioController::class, 'index'])->name('inicio');

// Cambiar el idioma (es, en, fr) y volver a la misma página
Route::get('/idioma/{codigo}', function (string $codigo) {
    if (array_key_exists($codigo, EstablecerIdioma::IDIOMAS)) {
        session(['idioma' => $codigo]);
    }

    return redirect()->back(fallback: route('inicio'));
})->name('idioma');

// Mapa del sitio para Google y otros buscadores
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Destinos: lista y página de cada destino (ej.: /destinos/jarabacoa)
Route::get('/destinos', [DestinoController::class, 'index'])->name('destinos.index');
Route::get('/destinos/{destino}', [DestinoController::class, 'show'])->name('destinos.show');

// Excursiones: catálogo con buscador y ficha de cada excursión
Route::get('/excursiones', [ExcursionController::class, 'index'])->name('excursiones.index');
Route::get('/excursiones/{excursion}', [ExcursionController::class, 'show'])->name('excursiones.show');

// Agencias: lista y perfil público de cada agencia (ej.: /agencias/2)
Route::get('/agencias', [AgenciaController::class, 'index'])->name('agencias.index');
Route::get('/agencias/{agencia}', [AgenciaController::class, 'show'])->whereNumber('agencia')->name('agencias.show');

// Asistente con IA: el turista hace preguntas abiertas (máximo 8 por minuto por persona)
Route::post('/asistente', [AsistenteController::class, 'responder'])
    ->middleware('throttle:8,1')
    ->name('asistente');

// Galería: todas las fotos de destinos, excursiones y agencias, con visor en grande
Route::get('/galeria', GaleriaController::class)->name('galeria');

// Servicios especiales: asesorías de viaje y renta de vehículos
Route::get('/asesorias', [ServicioController::class, 'asesorias'])->name('servicios.asesorias');
Route::get('/renta-de-vehiculos', [ServicioController::class, 'vehiculos'])->name('servicios.vehiculos');

// ---------- Etapa 5: registro e inicio de sesión ----------

// Solo para quien NO ha iniciado sesión
Route::middleware('guest')->group(function () {
    // La dirección es /login, igual que en la maqueta 18
    Route::get('/login', [SesionController::class, 'create'])->name('login');
    Route::post('/login', [SesionController::class, 'store'])
        ->middleware('throttle:6,1') // máximo 6 intentos por minuto (protege contra adivinar contraseñas)
        ->name('login.store');

    Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('registro.store');

    // Recuperar contraseña
    Route::get('/olvide-mi-contrasena', [ContrasenaController::class, 'solicitar'])->name('password.request');
    Route::post('/olvide-mi-contrasena', [ContrasenaController::class, 'enviarEnlace'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [ContrasenaController::class, 'formulario'])->name('password.reset');
    Route::post('/restablecer-contrasena', [ContrasenaController::class, 'restablecer'])->name('password.update');
});

// Cerrar sesión (solo quien inició sesión)
Route::post('/salir', [SesionController::class, 'destroy'])->middleware('auth')->name('logout');

// ---------- Paneles: el middleware «rol» revisa el rol antes de abrir cada uno ----------
Route::middleware(['auth', 'rol:turista'])->group(function () {
    Route::get('/mi-cuenta', [TuristaPanelController::class, 'index'])->name('turista.panel');

    // Etapa 7: el turista solicita y cancela reservas
    Route::post('/excursiones/{excursion}/reservar', [ReservaController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('reservas.store');
    Route::patch('/mi-cuenta/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

    // Favoritos: el corazón ♥ guarda o quita una excursión
    Route::post('/excursiones/{excursion}/favorito', [FavoritoController::class, 'alternar'])
        ->middleware('throttle:40,1')
        ->name('favoritos.alternar');

    // Reseñas: el turista opina de una excursión que ya hizo (y puede editarla o borrarla)
    Route::post('/excursiones/{excursion}/resena', [ResenaController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('resenas.store');
    Route::delete('/excursiones/{excursion}/resena', [ResenaController::class, 'destroy'])->name('resenas.destroy');

    // El turista pide una asesoría o un vehículo, y puede cancelar la solicitud
    Route::post('/solicitudes', [SolicitudController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('solicitudes.store');
    Route::patch('/mi-cuenta/solicitudes/{solicitud}/cancelar', [SolicitudController::class, 'cancelar'])->name('solicitudes.cancelar');
});

Route::middleware(['auth', 'rol:agencia'])->prefix('agencia')->name('agencia.')->group(function () {
    // Resumen
    Route::get('/panel', [AgenciaPanelController::class, 'index'])->name('panel');

    // Mi perfil: logo, presentación, fotos, videos y contacto
    Route::get('/perfil', [PerfilAgenciaController::class, 'edit'])->name('perfil');
    Route::put('/perfil', [PerfilAgenciaController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil/fotos/{indice}', [PerfilAgenciaController::class, 'borrarFoto'])
        ->whereNumber('indice')->name('perfil.foto.borrar');

    // Mis excursiones y paquetes: crear, ver lista, editar, eliminar
    Route::get('/excursiones', [ExcursionAgenciaController::class, 'index'])->name('excursiones.index');
    Route::get('/excursiones/nueva', [ExcursionAgenciaController::class, 'create'])->name('excursiones.create');
    Route::post('/excursiones', [ExcursionAgenciaController::class, 'store'])->name('excursiones.store');
    Route::get('/excursiones/{excursion}/editar', [ExcursionAgenciaController::class, 'edit'])->name('excursiones.edit');
    Route::put('/excursiones/{excursion}', [ExcursionAgenciaController::class, 'update'])->name('excursiones.update');
    Route::patch('/excursiones/{excursion}/disponible', [ExcursionAgenciaController::class, 'cambiarDisponible'])->name('excursiones.disponible');
    Route::delete('/excursiones/{excursion}', [ExcursionAgenciaController::class, 'destroy'])->name('excursiones.destroy');

    // Etapa 7: reservas que recibe la agencia (ver, confirmar, rechazar)
    Route::get('/reservas', [ReservaAgenciaController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/{reserva}', [ReservaAgenciaController::class, 'show'])->name('reservas.show');
    Route::patch('/reservas/{reserva}/confirmar', [ReservaAgenciaController::class, 'confirmar'])->name('reservas.confirmar');
    Route::patch('/reservas/{reserva}/rechazar', [ReservaAgenciaController::class, 'rechazar'])->name('reservas.rechazar');
});

Route::middleware(['auth', 'rol:administrador'])->prefix('admin')->name('admin.')->group(function () {
    // Resumen y supervisión de reservas
    Route::get('/', [AdminPanelController::class, 'index'])->name('panel');
    Route::get('/reservas', [AdminPanelController::class, 'reservas'])->name('reservas');

    // Usuarios y agencias: ver, crear, editar, suspender y borrar
    Route::get('/usuarios', [UsuarioAdminController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/nuevo', [UsuarioAdminController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioAdminController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [UsuarioAdminController::class, 'edit'])->whereNumber('usuario')->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuarioAdminController::class, 'update'])->whereNumber('usuario')->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/activo', [UsuarioAdminController::class, 'cambiarActivo'])->whereNumber('usuario')->name('usuarios.activo');
    Route::delete('/usuarios/{usuario}', [UsuarioAdminController::class, 'destroy'])->whereNumber('usuario')->name('usuarios.destroy');

    // Destinos: crear, editar y borrar
    Route::get('/destinos', [DestinoAdminController::class, 'index'])->name('destinos.index');
    Route::get('/destinos/nuevo', [DestinoAdminController::class, 'create'])->name('destinos.create');
    Route::post('/destinos', [DestinoAdminController::class, 'store'])->name('destinos.store');
    Route::get('/destinos/{destino}/editar', [DestinoAdminController::class, 'edit'])->name('destinos.edit');
    Route::put('/destinos/{destino}', [DestinoAdminController::class, 'update'])->name('destinos.update');
    Route::delete('/destinos/{destino}', [DestinoAdminController::class, 'destroy'])->name('destinos.destroy');

    // Excursiones de todas las agencias: moderar (ocultar o borrar)
    Route::get('/excursiones', [ExcursionAdminController::class, 'index'])->name('excursiones.index');
    Route::patch('/excursiones/{excursion}/disponible', [ExcursionAdminController::class, 'cambiarDisponible'])->name('excursiones.disponible');
    Route::delete('/excursiones/{excursion}', [ExcursionAdminController::class, 'destroy'])->name('excursiones.destroy');

    // Reporte de estadísticas (lo crea Python: python/reporte.py)
    Route::get('/reporte', [ReporteAdminController::class, 'index'])->name('reporte');
    Route::post('/reporte', [ReporteAdminController::class, 'generar'])->middleware('throttle:6,1')->name('reporte.generar');
    Route::get('/reporte/ver', [ReporteAdminController::class, 'ver'])->name('reporte.ver');

    // Reseñas: revisar, ocultar o borrar
    Route::get('/resenas', [ResenaAdminController::class, 'index'])->name('resenas.index');
    Route::patch('/resenas/{resena}/visible', [ResenaAdminController::class, 'cambiarVisible'])->name('resenas.visible');
    Route::delete('/resenas/{resena}', [ResenaAdminController::class, 'destroy'])->name('resenas.destroy');

    // Solicitudes de asesoría y renta de vehículos: ver y atender
    Route::get('/solicitudes', [SolicitudAdminController::class, 'index'])->name('solicitudes.index');
    Route::patch('/solicitudes/{solicitud}', [SolicitudAdminController::class, 'update'])->name('solicitudes.update');
});
