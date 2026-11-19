<?php

namespace App\Support\Asistente;

use App\Models\Destino;
use App\Models\Excursion;

/**
 * Prepara las instrucciones y la información de Explora RD que recibe la IA.
 *
 * La IA solo sabe lo que le damos aquí: los destinos, las excursiones publicadas
 * (con precio, agencia y enlace), los servicios y las preguntas frecuentes.
 * Así responde con datos reales de la página y no inventa.
 *
 * El texto siempre sale en el mismo orden y sin la hora: así la API puede
 * reutilizarlo de una pregunta a otra («caché»), lo que lo hace más rápido y barato.
 */
class ContextoTuristico
{
    public static function instrucciones(): string
    {
        $whatsapp = config('explora.whatsapp');

        return <<<TXT
        Eres el asistente virtual de Explora RD, una plataforma turística de República Dominicana
        que reúne destinos, excursiones y agencias turísticas locales.

        Tu trabajo: ayudar a los turistas a elegir destinos y excursiones y a organizar su viaje.

        Reglas:
        - Responde en el mismo idioma en que te escriben (español, inglés o francés).
        - Usa SOLO la información de Explora RD que aparece abajo para nombres, precios, duraciones y agencias.
          Si algo no está ahí, dilo con honestidad y sugiere escribir por WhatsApp (https://wa.me/{$whatsapp})
          o pedir una asesoría gratis en la página de Asesorías.
        - Nunca inventes precios, horarios, disponibilidad ni excursiones. Los precios son por persona y pueden variar: invita a confirmarlos con la agencia.
        - Cuando recomiendes una excursión o un destino, escribe su enlace completo tal como aparece abajo.
        - Tú no haces reservas ni cobras: explica que la reserva se pide en la ficha de la excursión (con una cuenta de turista) o por el WhatsApp de la agencia.
        - Nunca pidas datos de tarjetas, contraseñas ni documentos.
        - Puedes dar consejos generales de viaje por República Dominicana (clima, qué llevar, cultura), pero si te preguntan algo que no tiene que ver con turismo, explica amablemente que solo ayudas con viajes.
        - Escribe en texto simple, sin formato Markdown (sin asteriscos, almohadillas ni tablas). Puedes usar guiones para listas cortas.
        - Sé breve y cálido: máximo unas 120 palabras, salvo que te pidan más detalle.
        TXT;
    }

    /** La información de la página, en texto, para la IA. */
    public static function informacion(): string
    {
        $lineas = ['INFORMACIÓN DE EXPLORA RD', ''];

        $lineas[] = '== DESTINOS ==';
        foreach (Destino::orderBy('id')->get() as $d) {
            $actividades = implode('; ', array_slice($d->actividades ?? [], 0, 8));
            $lineas[] = "- {$d->nombre} ({$d->provincia}) · tipo: {$d->tipo} · ".route('destinos.show', $d);
            $lineas[] = '  '.$d->resumen().($actividades ? " Actividades: {$actividades}." : '');
        }

        $lineas[] = '';
        $lineas[] = '== EXCURSIONES, CIRCUITOS Y PAQUETES PUBLICADOS ==';
        $excursiones = Excursion::publicas()->conValoracion()->with(['agencia', 'destino'])->orderBy('id')->get();
        foreach ($excursiones as $e) {
            $estrellas = $e->totalResenas() ? " · {$e->promedioEstrellas()} estrellas ({$e->totalResenas()} reseñas)" : '';
            $lineas[] = "- {$e->titulo} · ".(Excursion::TIPOS[$e->tipo] ?? $e->tipo)
                .($e->categoria ? ' · '.(Excursion::CATEGORIAS[$e->categoria]['nombre'] ?? $e->categoria) : '')
                ." · destino: {$e->destino->nombre} · {$e->precioFormateado()} por persona"
                .($e->duracion ? " · duración: {$e->duracion}" : '')
                ." · agencia: {$e->agencia->nombreVisible()} (WhatsApp https://wa.me/{$e->whatsappAgencia()})"
                .$estrellas.' · '.route('excursiones.show', $e);
            $lineas[] = '  '.\Illuminate\Support\Str::limit($e->descripcion, 260);
        }

        $lineas[] = '';
        $lineas[] = '== SERVICIOS ==';
        $lineas[] = '- Asesoría de viaje gratis (un asesor de Explora RD contacta al turista): '.route('servicios.asesorias');
        $lineas[] = '- Renta de vehículos (económico, sedán, jeepeta, minivan o con chofer; recogida en aeropuertos y hoteles): '.route('servicios.vehiculos');
        $lineas[] = '- Catálogo de excursiones con filtros: '.route('excursiones.index');
        $lineas[] = '- Mapa de destinos: '.route('destinos.index');
        $lineas[] = '- Galería de fotos: '.route('galeria');
        $lineas[] = '- WhatsApp general de Explora RD: https://wa.me/'.config('explora.whatsapp');

        $lineas[] = '';
        $lineas[] = '== PREGUNTAS FRECUENTES ==';
        $lineas[] = '- Reservar: en la ficha de la excursión se elige fecha y personas y se envía la solicitud (hace falta una cuenta de turista); la agencia la confirma. También se puede escribir a la agencia por WhatsApp.';
        $lineas[] = '- Pagos: no se paga en Explora RD; el pago y las condiciones se acuerdan con la agencia.';
        $lineas[] = '- Cancelar: cada agencia tiene su política; el turista puede cancelar su solicitud desde «Mi cuenta» antes de la fecha.';
        $lineas[] = '- Reseñas: solo puede opinar quien ya hizo la excursión con una reserva confirmada.';
        $lineas[] = '- Idiomas de la página: español, inglés y francés.';

        return implode("\n", $lineas);
    }
}
