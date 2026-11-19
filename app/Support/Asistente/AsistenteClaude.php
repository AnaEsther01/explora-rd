<?php

namespace App\Support\Asistente;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Contracts\AsistenteTuristico;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * El asistente de verdad: le pregunta a Claude (la IA de Anthropic).
 *
 * Necesita dos cosas (ver docs en README.md, sección «Asistente con IA»):
 *   1. El paquete oficial:  composer require anthropic-ai/sdk
 *   2. La clave en .env:    ANTHROPIC_API_KEY=...
 * Si falta alguna, disponible() dice que no y la página ofrece WhatsApp.
 */
class AsistenteClaude implements AsistenteTuristico
{
    public function disponible(): bool
    {
        return (bool) config('explora.asistente.activo')
            && class_exists(Client::class)
            && filled(config('explora.asistente.clave'));
    }

    public function responder(array $conversacion): string
    {
        // La información de la página cambia poco: se guarda 10 minutos
        // (una copia por dirección del sitio, para que los enlaces apunten al mismo sitio que el turista usa)
        $informacion = Cache::remember('asistente.informacion.'.request()->getHttpHost(), 600, fn () => ContextoTuristico::informacion());

        try {
            // La clave sale de ANTHROPIC_API_KEY en .env (a través de config/explora.php)
            $cliente = new Client(apiKey: config('explora.asistente.clave'));

            $respuesta = $cliente->messages->create(
                model: config('explora.asistente.modelo'),
                maxTokens: 8000,
                // Instrucciones + información de la página. «cacheControl» le pide a la API
                // que la guarde un rato y no la vuelva a procesar en cada pregunta (más barato).
                system: [
                    ['type' => 'text', 'text' => ContextoTuristico::instrucciones()."\n\n".$informacion, 'cacheControl' => ['type' => 'ephemeral']],
                ],
                // Respuestas cortas de chat: con «low» piensa menos y responde más rápido
                outputConfig: ['effort' => 'low'],
                messages: $conversacion,
            );
        } catch (AuthenticationException $e) {      // 401: la clave está mal escrita o se borró
            throw new RuntimeException('La clave de Anthropic no es válida. Revisa ANTHROPIC_API_KEY en .env.', 0, $e);
        } catch (RateLimitException $e) {           // 429: demasiadas preguntas a la vez
            throw new RuntimeException('Demasiadas preguntas seguidas a la IA. Espera un momento.', 0, $e);
        } catch (APIStatusException $e) {           // otro error de la API (créditos agotados, servicio ocupado...)
            throw new RuntimeException('La API de Anthropic respondió con un error ('.($e->type?->value ?? 'desconocido').').', 0, $e);
        } catch (APIConnectionException $e) {       // sin internet o no se pudo conectar
            throw new RuntimeException('No se pudo conectar con la IA (revisa la conexión a internet).', 0, $e);
        }

        // La IA puede negarse a responder algo (por seguridad): mostramos un mensaje amable
        if ($respuesta->stopReason === 'refusal') {
            return __('Lo siento, no puedo ayudarte con eso. ¿Tienes alguna pregunta sobre tu viaje por República Dominicana?');
        }

        $texto = '';
        foreach ($respuesta->content as $bloque) {
            if ($bloque->type === 'text') {   // se ignoran los bloques de «pensamiento»
                $texto .= $bloque->text;
            }
        }

        return trim($texto) !== '' ? trim($texto) : __('No tengo una respuesta para eso. Escríbenos por WhatsApp y te ayudamos.');
    }
}
