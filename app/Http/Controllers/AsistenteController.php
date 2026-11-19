<?php

namespace App\Http\Controllers;

use App\Contracts\AsistenteTuristico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * POST /asistente — el chat «Pregúntale a Explora RD».
 *
 * La ventana del chat (public/js/asistente.js) envía la conversación
 * y recibe la respuesta de la IA en JSON, sin recargar la página.
 * No se guarda nada en la base de datos.
 */
class AsistenteController extends Controller
{
    public function responder(Request $request, AsistenteTuristico $asistente)
    {
        $datos = $request->validate([
            'mensajes' => ['required', 'array', 'min:1', 'max:30'],
            'mensajes.*.role' => ['required', Rule::in(['user', 'assistant'])],
            'mensajes.*.content' => ['required', 'string', 'max:4000'],
        ]);

        // Solo los mensajes más recientes (para no enviar conversaciones enormes)
        $mensajes = array_slice($datos['mensajes'], -config('explora.asistente.max_mensajes', 12));

        // La conversación debe empezar y terminar con un mensaje del turista
        while ($mensajes && $mensajes[0]['role'] !== 'user') {
            array_shift($mensajes);
        }
        $ultimo = end($mensajes);
        if (! $ultimo || $ultimo['role'] !== 'user') {
            return response()->json(['message' => __('Escribe tu pregunta.')], 422);
        }
        if (mb_strlen($ultimo['content']) > 1000) {
            return response()->json(['message' => __('Tu pregunta es muy larga. Escríbela en menos de 1000 letras.')], 422);
        }

        if (! $asistente->disponible()) {
            return response()->json([
                'disponible' => false,
                'respuesta' => __('El asistente con IA todavía no está activado. Escríbenos por WhatsApp y te respondemos enseguida.'),
            ]);
        }

        try {
            $respuesta = $asistente->responder(array_map(
                fn ($m) => ['role' => $m['role'], 'content' => $m['content']],
                array_values($mensajes)
            ));
        } catch (\Throwable $e) {
            report($e); // queda anotado en storage/logs/laravel.log para revisarlo

            return response()->json([
                'disponible' => true,
                'error' => true,
                'respuesta' => __('Ahora mismo no puedo responder. Inténtalo de nuevo en un momento o escríbenos por WhatsApp.'),
            ], 503);
        }

        return response()->json(['disponible' => true, 'respuesta' => $respuesta]);
    }
}
