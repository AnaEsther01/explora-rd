{{-- Textos que usa public/js/app.js, ya traducidos al idioma elegido --}}
@php
    $textosJs = [
        'pestanas' => [
            'experiencias' => ['destino' => __('Destino'), 'ayuda' => __('¿A dónde quieres ir?'), 'fecha' => __('Fechas'), 'personas' => __('Personas')],
            'circuitos' => ['destino' => __('Región o ruta'), 'ayuda' => __('¿Qué zona quieres recorrer?'), 'fecha' => __('Fecha de salida'), 'personas' => __('Viajeros')],
            'asesorias' => ['destino' => __('Destino de interés'), 'ayuda' => __('¿Qué te gustaría conocer?'), 'fecha' => __('Fecha del viaje'), 'personas' => __('Viajeros')],
            'vehiculos' => ['destino' => __('Lugar de recogida'), 'ayuda' => __('Aeropuerto, ciudad u hotel'), 'fecha' => __('Fecha de recogida'), 'personas' => __('Pasajeros')],
        ],
        'fecha' => __('Fecha'),
        'personas' => __('Personas'),
        'consulta' => __('¡Hola! Vi en Explora RD «:titulo» y quisiera más información.'),
        'cualquiera' => __('Cualquiera'),
        'guardarFavorito' => __('Guardar en favoritos'),
        'quitarFavorito' => __('Quitar de favoritos'),
        'mostrar' => __('Mostrar contraseña'),
        'ocultar' => __('Ocultar contraseña'),
        'responsable' => __('Nombre del responsable'),
        'nombreCompleto' => __('Nombre completo'),
        'registrarAgencia' => __('Registrar mi agencia'),
        'crearCuenta' => __('Crear mi cuenta'),
    ];
@endphp
{{-- (No usamos @json porque Blade se confunde con arreglos de varias líneas) --}}
<script>
    window.TEXTOS = {!! json_encode($textosJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!};
</script>
