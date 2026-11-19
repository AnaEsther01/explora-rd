<!DOCTYPE html>
<html lang="es">
{{--
    Plantilla de las páginas de error (404, 403, 500...).
    Es sencilla a propósito: no usa la base de datos ni la sesión,
    así se puede mostrar aunque algo esté fallando.
--}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') — Explora RD</title>
    <link rel="icon" type="image/svg+xml" href="/img/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; font-family: 'Poppins', system-ui, sans-serif; color: #0B3C49; text-align: center;
            background: linear-gradient(180deg, #5DB8C8 0%, #18A0A6 55%, #0F8A92 72%, #E9D7AE 72.3%, #F1E3C6 100%);
        }
        .tarjeta { background: #FFFFFF; border-radius: 22px; padding: 44px 32px; max-width: 520px; width: 100%; box-shadow: 0 22px 50px rgba(11, 60, 73, .22); }
        .logo { display: inline-flex; align-items: center; gap: 10px; color: #0B3C49; text-decoration: none; font-weight: 600; font-size: 1.15rem; }
        .codigo { font-family: 'Lora', Georgia, serif; font-size: 5rem; line-height: 1; color: #0E8A92; margin: 24px 0 8px; }
        h1 { font-family: 'Lora', Georgia, serif; font-size: 1.6rem; margin: 0 0 12px; }
        p { color: #5B6B70; line-height: 1.6; margin: 0 0 28px; }
        .botones { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .boton { display: inline-block; padding: 12px 22px; border-radius: 12px; font-weight: 600; text-decoration: none; }
        .principal { background: #0E8A92; color: #FFFFFF; }
        .principal:hover { background: #0A6F76; }
        .secundario { border: 1.5px solid #0E8A92; color: #0E8A92; }
    </style>
</head>
<body>
    <main class="tarjeta">
        <a href="/" class="logo"><img src="/img/logo.svg" alt="" width="40" height="40"> Explora RD</a>
        <div class="codigo">@yield('codigo')</div>
        <h1>@yield('titulo')</h1>
        <p>@yield('mensaje')</p>
        <div class="botones">
            <a href="/" class="boton principal">Ir al inicio</a>
            @yield('boton_extra')
        </div>
    </main>
</body>
</html>
