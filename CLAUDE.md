# Explora RD — Contexto del proyecto

Plataforma turística de República Dominicana. Proyecto escolar de Ana Esther Hernández Hernández
(5to A, Politécnico Cristiano James Davis, prof. Luis Margarin), pensado para venderse a una agencia de turismo.

**Objetivo:** que cualquier persona pueda descubrir destinos, encontrar excursiones y contactar
directamente con agencias turísticas desde un solo lugar, en computadora o celular.

## Cómo trabajar conmigo
- Soy estudiante: explica cada paso en español sencillo y di qué archivo se crea o se cambia y por qué.
- Trabaja por etapas (ver abajo). No adelantes etapas sin que yo lo pida.
- Pregunta antes de instalar paquetes nuevos o de borrar archivos.
- Al terminar cada etapa, dime cómo probarla (`php artisan serve` → http://127.0.0.1:8000).

## Tecnologías
- Laravel 13 (PHP 8.3), vistas con Blade.
- Servidor local: **WampServer** en Windows (Apache + MySQL + phpMyAdmin). Base de datos: `explora_rd`.
  - PHP 8.3 o más reciente seleccionado en WampServer y agregado al PATH de Windows (lo necesitan Composer y `php artisan`).
  - MySQL: usuario `root`, sin contraseña por defecto, puerto 3306 (WampServer también puede traer MariaDB en el 3307: usar MySQL).
  - El icono de WampServer debe estar en verde antes de trabajar.
- Bootstrap 5, HTML, CSS y JavaScript. Diseño adaptable (responsive) a celular, tableta y computadora.
- Mapa de destinos: Leaflet 1.9.4 + OpenStreetMap, cargados desde CDN (no se instala nada; necesitan internet).
- Python solo para funciones complementarias: reporte de estadísticas con matplotlib (Python 3.14 instalado; matplotlib ya estaba).
- Editor: Visual Studio Code. Control de versiones: Git y GitHub.

## Identidad visual
- Estilo: moderno, elegante y tropical.
- Colores: turquesa `#0E8A92`, azul oscuro `#0B3C49`, blanco `#FFFFFF`, arena `#F1E3C6`.
- Letras (Google Fonts): **Lora** para títulos y **Poppins** para textos y botones.
- Logo: una palmera dentro de un círculo turquesa junto al nombre «Explora RD».
- Imágenes: se guardan en `public/img`. Usa marcadores de posición hasta que yo ponga fotos propias o con permiso.
  - Ya hay fotos gratuitas de Unsplash y Pexels (descargadas con mi permiso). Cada foto nueva se anota en `public/img/CREDITOS.txt` (ruta, autor y enlace separados por dos o más espacios); la galería lee ese archivo.

## Referencias visuales (léelas antes de diseñar)
- `referencias/01` a `16`: el documento del proyecto con cómo debe verse y qué debe tener cada sección. **La página debe parecerse a esto.**
  - `02_pagina_principal_diseno.png` es el diseño de referencia de la página principal. Copia su estilo (portada con foto tropical, título grande con serifa, pestañas y buscador blanco flotante), pero con el nombre y el logo de Explora RD. No uses el nombre ni el logo de la página de ejemplo.
  - `07_espacio_agencias_panel.png` es la referencia del panel privado de las agencias, adaptado a excursiones y reservas.
- `referencias/17` a `20`: maqueta de la página principal y del inicio de sesión, boceto del panel de agencia y diagrama Entidad–Relación.
- Las fotos de las referencias son solo de ejemplo: no las copies al proyecto. Usa marcadores de posición en `public/img` hasta que yo ponga fotos propias o con permiso.
- `documentacion/`: los Pasos 1 al 6 del proyecto. Si hay dudas sobre requerimientos, tablas o pantallas, sigue lo que dicen esos documentos.

## Carpeta del proyecto
Esta carpeta ya contiene `CLAUDE.md`, `referencias/` y `documentacion/`. Para crear Laravel aquí, créalo en una subcarpeta temporal y mueve los archivos a esta carpeta, sin borrar ni sobrescribir esos tres elementos.

## Usuarios y roles
Hay una sola tabla `usuarios`, con un campo `rol` que puede ser `turista`, `agencia` o `administrador`.
- **Turista:** explora destinos, busca excursiones, ve fichas, contacta por WhatsApp y solicita reservas, asesorías o renta de vehículos.
- **Agencia:** edita su perfil (logo, presentación, fotos, videos, contacto, WhatsApp), publica excursiones y paquetes, y gestiona sus reservas.
- **Administrador:** administra usuarios, agencias, destinos y contenido general, y supervisa las reservas.
- Un middleware revisa el rol antes de abrir cada panel.

## Base de datos
Usa estos nombres en español, igual que en la documentación del proyecto.
- `usuarios`: id, nombre, correo (único), contrasena, telefono, rol. Si es agencia: nombre_comercial, logo, descripcion, whatsapp, direccion.
- `destinos`: id, nombre, provincia, descripcion, tipo (playa/montaña/ciudad), foto, latitud, longitud (para el mapa).
- `excursiones`: id, agencia_id (FK → usuarios), destino_id (FK → destinos), tipo (excursion/circuito/paquete), titulo, descripcion, precio DECIMAL(10,2), duracion, ubicacion, disponible.
- `reservas`: id, usuario_id (FK → usuarios), excursion_id (FK → excursiones), fecha, cantidad_personas, estado (pendiente/confirmada/cancelada), comentario.
- Tablas agregadas después del diseño del Paso 5:
  - `solicitudes`: id, usuario_id (FK → usuarios), servicio (asesoria/vehiculo), lugar, fecha_inicio, fecha_fin, cantidad_personas, tipo_vehiculo, telefono, mensaje, estado (pendiente/atendida/cancelada), respuesta. Las atiende el administrador.
  - `resenas`: id, usuario_id (FK → usuarios), excursion_id (FK → excursiones), estrellas (1 a 5), comentario, visible. Solo opina quien tiene una reserva confirmada con fecha pasada; una por turista y excursión.
  - `favoritos`: id, usuario_id (FK → usuarios), excursion_id (FK → excursiones). Tabla intermedia; una por turista y excursión.
- Relaciones: una agencia tiene muchas excursiones (1:N), un destino tiene muchas excursiones (1:N), una excursión tiene muchas reservas y reseñas (1:N), un turista tiene muchas reservas, solicitudes y reseñas (1:N), y turistas y excursiones se unen por `favoritos` (N:M).
- Diagrama Entidad–Relación actualizado: `docs/diagrama_entidad_relacion.png` (y `.svg`). Si cambian las tablas, hay que actualizarlo.
- Datos de prueba (seeders): 12 destinos con fotos y coordenadas (entre ellos Jarabacoa, Bahía de las Águilas, Punta Cana y Santo Domingo), excursiones, reservas, solicitudes, reseñas y favoritos. Cuentas de prueba en `UsuarioSeeder.php`. Para la página publicada se usa `ProduccionSeeder` (solo destinos).

## Páginas
1. **Página principal:** logo, menú, idioma, botón Entrar, portada tropical con el título «Descubre República Dominicana», pestañas (Experiencias, Circuitos y paquetes, Asesorías, Renta de vehículos), buscador (destino, fechas, personas), destinos y excursiones destacados.
2. **Destinos:** cada destino tiene su página con fotos, descripción, actividades y excursiones.
3. **Excursiones y experiencias:** playas, montaña, catamarán, cultura, gastronomía, familia y aventura. Cada ficha muestra fotos, descripción, precio, duración, ubicación y contacto de la agencia.
4. **Perfil de agencia** (página pública) y **panel privado de la agencia**.
5. **Circuitos y paquetes:** por ejemplo, Ruta de aventura por Jarabacoa, Recorrido cultural por Santo Domingo y Playa en Samaná.
6. **Asesorías** y **Renta de vehículos**.
7. **Otras secciones:** Sobre nosotros, Por qué elegirnos, Galería, Preguntas frecuentes, Contacto y pie de página.
8. **Botón flotante de WhatsApp** (enlace `https://wa.me/`).
9. **Inicio de sesión y registro** (turista o agencia) y **panel del administrador**.
10. **Idiomas:** español por defecto; preparada para inglés y francés con la localización de Laravel.
    - Hecho: textos en `lang/en.json` y `lang/fr.json` (la clave es la frase en español). Toda frase nueva con `__('...')` en páginas públicas debe traducirse: la prueba `IdiomasTest` falla si falta alguna. Los paneles de agencia y administrador quedan en español.
11. **Agregado después:** reseñas con estrellas, favoritos (corazón), mapa de destinos (`/destinos` y cada destino), página de galería (`/galeria`, con créditos leídos de `public/img/CREDITOS.txt`) y páginas `/asesorias` y `/renta-de-vehiculos` con formulario.

## Etapas de trabajo
1. Preparación del entorno (Laravel, MySQL, herramientas) ✅
2. Base de datos (migraciones, modelos, seeders) ✅
3. Diseño de la página principal ✅
4. Destinos y excursiones ✅
5. Registro e inicio de sesión con roles ✅
6. Panel de agencias ✅
7. Sistema de reservas ✅
8. Pruebas y publicación ✅ (pruebas hechas; la publicación en internet sigue `GUIA_PUBLICACION.md` cuando haya hosting)

Extras ya hechos: administrador completo, inglés y francés, asesorías y renta, reseñas, favoritos, mapa, galería y documentación actualizada (`README.md`, `PLAN_DE_PRUEBAS.md`, `docs/`).

Python (hecho): `python/reporte.py` crea el reporte de estadísticas con matplotlib a partir de `storage/app/reportes/datos.json`, que prepara `php artisan explora:reporte` (`app/Support/Estadisticas.php`). Se ve en el panel del administrador → «Reporte (Python)». Explicación en `python/LEEME.md`; pruebas con `python -m unittest discover -s python`. No instalar paquetes nuevos de Python sin preguntar.

Asistente con IA (hecho): chat «Pregúntale a Explora RD» en las páginas públicas (`partials/asistente.blade.php`, `public/js/asistente.js`, `POST /asistente`). Usa Claude Opus 5.5 con el paquete oficial `anthropic-ai/sdk` (`app/Support/Asistente/AsistenteClaude.php`) y solo la información real de la página (`ContextoTuristico.php`). El paquete ya está instalado (`anthropic-ai/sdk` ^1.11, 19/11/2026; la conexión con Anthropic se probó con una clave falsa: responde 401 como se espera). Decidí dejarlo como «Próximamente» (no voy a pagar la IA por ahora): sin `ANTHROPIC_API_KEY` en `.env` el botón dice «Asistente IA · Próximamente» y solo ofrece WhatsApp. Si algún día pongo la clave, se activa solo. Si Composer se queda pegado al final, es la revisión de seguridad por internet: usar `--no-audit`. Las pruebas usan un asistente falso (no gastan).

Publicar gratis (preparado): InfinityFree con subdominio gratis. `scripts\preparar-infinityfree.ps1` crea `publicar\` (archivos listos con `vendor` sin dev, `.htaccess` que manda todo a `public/`, `.env` de producción con `FOTOS_EN_PUBLIC=true` y `MAIL_MAILER=log`, y `base_de_datos.sql` con las tablas y los 12 destinos). Guía: `GUIA_INFINITYFREE.md`. Falta que yo cree la cuenta, la base de datos y suba los archivos.

Pendiente (solo si lo pido): decidir si se borran `resources/views/welcome.blade.php` y `database/database.sqlite` (no se usan).

## Pruebas
- `php artisan test` debe pasar completo antes de dar algo por terminado (146 pruebas de Laravel y 8 de Python al 19/11/2026). Usa SQLite en memoria: no toca `explora_rd`.
- Al agregar una función, agregar sus pruebas en `tests/Feature/` y actualizar `PLAN_DE_PRUEBAS.md`.
