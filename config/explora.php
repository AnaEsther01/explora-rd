<?php

/*
| Datos de contacto generales de Explora RD.
| Se cambian en el archivo .env sin tocar el código.
*/

return [
    // Número de WhatsApp con código de país, sin espacios ni signos (ej.: 18095550000)
    'whatsapp' => env('EXPLORA_WHATSAPP', '18095550000'),
    'correo' => env('EXPLORA_CORREO', 'info@explorard.test'),
    'telefono' => env('EXPLORA_TELEFONO', '809-555-0000'),
    'direccion' => env('EXPLORA_DIRECCION', 'Santo Domingo, República Dominicana'),

    // Programa de Python para el reporte de estadísticas (python/reporte.py).
    // En Windows suele ser «python»; en algunos servidores, «python3».
    'python' => env('EXPLORA_PYTHON', 'python'),

    // Asistente con IA para las preguntas abiertas de los turistas (Claude, de Anthropic).
    // Necesita el paquete «anthropic-ai/sdk» y una clave de console.anthropic.com.
    'asistente' => [
        'activo' => env('EXPLORA_IA_ACTIVO', true),
        'clave' => env('ANTHROPIC_API_KEY'),
        'modelo' => env('EXPLORA_IA_MODELO', 'claude-opus-5-5'),
        // Mensajes que se envían como máximo (los más recientes de la conversación)
        'max_mensajes' => 12,
    ],
];
