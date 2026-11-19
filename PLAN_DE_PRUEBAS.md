# ✅ Plan de pruebas — Explora RD

Este documento sirve para la **Etapa 8 (Pruebas y publicación)** y para la documentación del proyecto.
Tiene dos partes: las **pruebas automáticas** (las hace la computadora) y las **pruebas manuales** (las hace una persona en el navegador).

---

## 1. Pruebas automáticas

Se ejecutan con:
```bash
php artisan test
```
Usan una base de datos **temporal** en memoria: no tocan `explora_rd`.

| Archivo | Qué revisa | Pruebas |
|---|---|---|
| `ExampleTest.php` | La página principal carga con destinos, excursiones y WhatsApp | 1 |
| `DestinosExcursionesTest.php` | Destinos, filtros, ficha, búsqueda, categorías, precio | 10 |
| `AutenticacionTest.php` | Registro, inicio de sesión, roles, middleware, recuperar contraseña | 14 |
| `PanelAgenciaTest.php` | Perfil de agencia, fotos, videos, crear/editar/ocultar/eliminar excursiones | 15 |
| `ReservasTest.php` | Solicitar, cancelar, confirmar y rechazar reservas; supervisión | 16 |
| `AdministradorTest.php` | Panel del administrador: usuarios, suspender cuentas, destinos y moderar excursiones | 11 |
| `IdiomasTest.php` | Español, inglés y francés: cambiar de idioma, páginas, destinos, errores de formularios y que **todas las frases** tengan traducción | 8 |
| `RecorridoCompletoTest.php` | Todas las páginas cargan, error 404, seguridad y el **recorrido completo** del diagrama de flujo | 5 |
| `SolicitudesTest.php` | Asesorías y renta de vehículos: páginas, formulario lleno desde la portada, datos revisados, límite de pendientes, cancelar, el administrador responde | 11 |
| `ResenasTest.php` | Reseñas: promedio y estrellas, solo opina quien ya hizo la excursión, editar, borrar, ocultar (administrador), orden «mejor valoradas» | 10 |
| `FavoritosTest.php` | Favoritos: guardar y quitar sin recargar, «Mis favoritos», excursiones ocultas, solo turistas, contador en el panel de la agencia | 8 |
| `MapaTest.php` | Mapa: todos los destinos con su ubicación, filtro por tipo, mapa de cada destino, «Cómo llegar», coordenadas revisadas en el administrador y en los datos de producción | 7 |
| `GaleriaTest.php` | Galería: fotos de destinos y excursiones, crédito de cada autor, sin fotos repetidas ni de excursiones ocultas | 6 |
| `ReporteTest.php` | Reporte: estadísticas correctas, datos en JSON, el administrador lo genera y lo ve, aviso si Python falla o si el servidor no deja ejecutarlo, solo el administrador, y una prueba con **Python de verdad** | 9 |
| `AsistenteTest.php` | Asistente con IA (con un asistente falso, sin gastar): responde, envía solo los mensajes recientes, revisa los datos, sin clave ofrece WhatsApp, error amable, límite por minuto, la IA recibe la información real, el chat no sale en los paneles, modo «Próximamente» sin clave, traducciones | 12 |
| `FotosEnPublicTest.php` | Hosting sin terminal: con `FOTOS_EN_PUBLIC=true` las fotos van a `public/storage` | 2 |
| `Unit/ExampleTest.php` | Prueba básica de Laravel | 1 |
| **Total** | | **146 (631 comprobaciones)** — ✅ todas pasan |

### Pruebas del programa de Python

```bash
python -m unittest discover -s python -v
```

| Archivo | Qué revisa | Pruebas |
|---|---|---|
| `python/test_reporte.py` | Formato del dinero, títulos largos, conclusiones con los números correctos, 5 gráficas en la página, nombres con símbolos (`<`, `>`) que no rompen la página, plataforma sin datos, leer el JSON y guardar el HTML, aviso si falta el archivo | 8 — ✅ todas pasan |

---

## 2. Pruebas manuales por requerimiento (Paso 4)

Marca la última columna al probar. Cuentas de prueba: ver `database/seeders/UsuarioSeeder.php`.

| Código | Requerimiento | Pasos | Resultado esperado | ✓ |
|---|---|---|---|---|
| RF-01 | Registrarse e iniciar sesión | 1) `/registro` → «Soy turista» → llenar → «Crear mi cuenta». 2) Cerrar sesión. 3) `/login` con ese correo | Entra a «Mi cuenta». Con contraseña equivocada sale «El correo o la contraseña no son correctos.» | |
| RF-01 | Registro de agencia | `/registro?tipo=agencia` → llenar nombre de la agencia y WhatsApp | Entra al «Panel de la agencia» | |
| RF-01 | Roles (middleware) | Entrar como agencia y escribir `/admin` en la barra | Vuelve a su panel con «No tienes permiso para entrar a esa sección.» | |
| RF-02 | Buscar experiencias | En la portada escribir «Punta Cana», elegir fecha y personas → «Buscar» | Catálogo con Catamarán a isla Saona y Buggies y playa Macao | |
| RF-02 | Filtros | En `/excursiones` elegir «Gastronomía», luego precio máximo RD$ 3,000 | Solo aparece la Ruta gastronómica | |
| RF-03 | Destinos | Menú «Destinos» → «Montaña» → Jarabacoa | Página con foto, descripción, «Qué hacer», excursiones y otros destinos | |
| RF-04 | Ficha y WhatsApp | Abrir una excursión → cambiar personas → «Consultar por WhatsApp» | El total cambia; WhatsApp abre con el mensaje, la fecha y las personas | |
| RF-05 | Perfil de la agencia | Panel → «Mi perfil» → subir logo y una foto → Guardar → «Ver mi perfil público» | Se ven el logo, la foto y los datos nuevos | |
| RF-06 | Publicar excursión | Panel → «+ Nueva excursión» → llenar → Publicar | Aparece en «Mis excursiones» y en `/excursiones` | |
| RF-06 | Ocultar y eliminar | Apagar el interruptor «Publicada»; luego eliminar una excursión sin reservas | Oculta: ya no sale en el catálogo. Eliminar una con reservas: lo impide con un aviso | |
| RF-07 | Solicitar reserva | Como turista: ficha → fecha, personas, comentario → «Solicitar reserva» | «Mi cuenta» muestra la reserva **Pendiente** | |
| RF-07 | Confirmar / Rechazar | Como la agencia de esa excursión: campanita 🔔 → «Confirmar» | La reserva pasa a **Confirmada**; el turista la ve confirmada; llega el correo (ver `storage/logs/laravel.log`) | |
| RF-07 | Cancelar | Como turista: «Cancelar reserva» | Pasa a **Cancelada** | |
| RF-08 | Supervisar reservas | Como administrador: «Reservas» → filtrar por agencia | Lista de reservas de esa agencia | |
| RF-08 | Administrar usuarios | «Usuarios y agencias» → «Nuevo» → crear una agencia; luego apagar su interruptor «Activa» | La agencia queda **suspendida**: no puede entrar y su perfil y excursiones no se ven | |
| RF-08 | Administrar destinos | «Destinos» → «Nuevo destino» con foto y actividades | Aparece en `/destinos` con su página | |
| RF-08 | Moderar contenido | «Excursiones» → apagar «Visible» en una excursión | Ya no sale en el catálogo | |

### Funciones agregadas después del diseño

| Código | Función | Pasos | Resultado esperado | ✓ |
|---|---|---|---|---|
| EX-01 | Asesoría desde la portada | Portada → pestaña «Asesorías» → escribir «Samaná», fecha y personas → «Buscar» | Abre `/asesorias` con el formulario ya lleno | |
| EX-01 | Pedir un vehículo | Como turista: `/renta-de-vehiculos` → «Pedir este» en Jeepeta → completar fechas y teléfono → «Enviar solicitud» | «Mi cuenta» → «Mis solicitudes» la muestra **Pendiente** | |
| EX-01 | Atender una solicitud | Como administrador: «Asesorías y vehículos» → cambiar a «Atendida» y escribir una respuesta → «Guardar» | El turista ve **Atendida** y la respuesta en «Mi cuenta» | |
| EX-02 | Escribir una reseña | Como `maria@correo.test`: «Mi cuenta» → «Escribir reseña» en Buggies y playa Macao → elegir estrellas → «Publicar reseña» | La ficha muestra la reseña, el promedio y las barras; la tarjeta muestra ★ | |
| EX-02 | Solo opina quien fue | Como turista, abrir una excursión que nunca reservó | No aparece el formulario; dice «Podrás opinar cuando hayas hecho esta excursión…» | |
| EX-02 | Moderar reseñas | Como administrador: «Reseñas» → apagar «Visible» en una | Deja de verse en la ficha y no cuenta en el promedio | |
| EX-03 | Favoritos | Como turista: tocar el corazón ♡ de una tarjeta | Se pone rojo ♥ sin recargar la página y aparece en «Mi cuenta» → «Mis favoritos» | |
| EX-03 | Favoritos sin sesión | Sin iniciar sesión, tocar el corazón | Lleva a «Iniciar sesión» y luego vuelve a la misma página | |
| EX-04 | Mapa de destinos | `/destinos` → tocar un punto del mapa → «Ver destino» | Se abre un globo con foto y nombre; el enlace lleva al destino | |
| EX-04 | Ubicación de un destino | Abrir un destino → «Cómo llegar» | Abre Google Maps con la ruta al lugar | |
| EX-04 | Coordenadas | Como administrador: editar un destino con longitud `70.5` (sin el signo menos) | Error: debe estar entre -72.1 y -68.2 | |
| EX-05 | Galería | `/galeria` → filtro «Montaña» → tocar una foto → flechas ← → | Solo fotos de montaña; el visor muestra foto, lugar, autor y contador «2 / 5» | |
| EX-05 | Galería en el celular | En modo celular, abrir una foto y deslizar con el dedo | Pasa a la siguiente foto | |
| EX-06 | Reporte con Python | Como administrador: «Reporte (Python)» → «Generar reporte» | Aparecen las tarjetas, las conclusiones, 5 gráficas y la tabla de agencias, con la hora de actualización | |
| EX-06 | Reporte desde la terminal | `php artisan explora:reporte` | Dice «1/2 Datos guardados…» y «2/2 Reporte creado…» | |
| EX-07 | Asistente sin clave | Sin `ANTHROPIC_API_KEY` en `.env`: tocar ✨ «Asistente IA · Próximamente» | Se abre el aviso «Muy pronto podrás preguntarle a nuestra IA» con el botón de WhatsApp; no hay caja para escribir | |
| EX-07 | Asistente con clave | Con el paquete instalado y la clave: preguntar «¿Qué hago en Punta Cana?» | Responde con excursiones reales de Punta Cana y sus enlaces | |
| EX-07 | Fuera de tema | Preguntar algo que no es de turismo | Explica amablemente que solo ayuda con viajes | |
| EX-06 | Los números cambian | Confirmar una reserva como agencia → volver a generar el reporte | Suben «Confirmadas» y los ingresos estimados de esa agencia | |

---

## 3. Pruebas de diseño (responsive)

| Prueba | Cómo | Esperado | ✓ |
|---|---|---|---|
| Celular | Chrome → `F12` → icono de celular (`Ctrl + Shift + M`) → iPhone o Galaxy | El menú se vuelve ☰; nada se sale de la pantalla | |
| Tableta | Igual, con iPad | Las tarjetas se acomodan en 2 columnas | |
| Computadora | Pantalla completa | Igual a las maquetas 17, 18 y al boceto 19 | |
| Fotos | Recorrer todas las páginas | No hay imágenes rotas | |

## 4. Pruebas de seguridad

| Prueba | Esperado | ✓ |
|---|---|---|
| Abrir `/agencia/panel` sin iniciar sesión | Lleva a «Iniciar sesión» | |
| Una agencia intenta editar una excursión de otra (cambiando la dirección) | Error 403 «No tienes permiso» | |
| Intentar entrar 7 veces seguidas con contraseña equivocada | «Demasiados intentos» (espera de 1 minuto) | |
| Subir un archivo que no es imagen como foto | «… debe ser una imagen» | |
| Abrir una dirección que no existe | Página «Página no encontrada» de Explora RD | |
| Una agencia intenta enviar una solicitud o guardar un favorito | Vuelve a su panel sin guardar nada | |
| Un turista intenta cancelar la solicitud de otro (cambiando el número en la dirección) | Error 403 | |
| Enviar más de 3 solicitudes pendientes seguidas | «Ya tienes 3 solicitudes pendientes…» | |

---

## 5. Resultados de la revisión técnica (Etapa 8)

- ✅ 146 pruebas de Laravel y 8 de Python pasan (eran 81 al terminar la Etapa 8; el resto se agregó con las funciones nuevas).
- ✅ `composer audit`: **ninguna vulnerabilidad** conocida en los paquetes.
- ✅ `php artisan optimize` funciona (la página está lista para el modo rápido de producción).
- ✅ Todas las páginas revisadas en computadora y celular: nada se sale de la pantalla y la consola no muestra errores.
- ✅ Cabeceras de seguridad, páginas de error en español, mapa del sitio (`/sitemap.xml`) y `robots.txt`.
- ✅ Las páginas nuevas (asesorías, renta, galería, mapa, reseñas y favoritos) revisadas en computadora y celular.
- ⚠️ El mapa necesita internet: Leaflet y los mapas de OpenStreetMap se cargan desde la red. Sin conexión aparece «Cargando el mapa…» y el resto de la página funciona igual.
