<?php

namespace App\Contracts;

/**
 * El asistente que responde las preguntas abiertas de los turistas.
 *
 * Es un «contrato» (interface): dice QUÉ hace el asistente, no CÓMO.
 * La versión de verdad usa Claude (App\Support\Asistente\AsistenteClaude);
 * en las pruebas automáticas se usa una versión falsa que no gasta dinero.
 */
interface AsistenteTuristico
{
    /** ¿Está listo para responder? (paquete instalado y clave escrita en .env) */
    public function disponible(): bool;

    /**
     * Responde la última pregunta del turista.
     *
     * @param  array<int, array{role: string, content: string}>  $conversacion  mensajes en orden; el último es del turista
     * @return string  la respuesta en texto plano
     */
    public function responder(array $conversacion): string;
}
