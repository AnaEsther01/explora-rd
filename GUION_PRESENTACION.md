# 🎤 Guion de la presentación — Explora RD

**Ana Esther Hernández Hernández** · 5to A · Politécnico Cristiano James Davis · Prof. Luis Margarin

Duración aproximada: **15 minutos** de presentación + preguntas.
Las frases en *cursiva* son ideas de lo que puedes decir: dilas con tus propias palabras, no hace falta aprenderlas de memoria.

---

## ✅ Antes de presentar (el día antes y 15 minutos antes)

**El día antes**
- [ ] Ejecutar las pruebas y confirmar que todo pasa:
  ```
  php artisan test
  python -m unittest discover -s python
  ```
- [ ] Generar el reporte de Python: `php artisan explora:reporte`
- [ ] Si ya escribiste la reseña de María en «Buggies y playa Macao», **bórrala** (entra como María → abre Buggies → «Borrar»), para poder escribirla en vivo.
- [ ] Tener el diagrama abierto: `docs/diagrama_entidad_relacion.png`.

**15 minutos antes**
- [ ] WampServer encendido (icono **verde**).
- [ ] En la terminal, dentro de la carpeta del proyecto: `php artisan serve`
- [ ] **Internet** conectado (lo necesitan el mapa y las letras de Google).
  ⚠️ En la escuela, Fortinet puede bloquear algunas páginas: prueba el mapa allí antes de empezar.
- [ ] Abrir **dos ventanas** del navegador, para no tener que cerrar sesión a cada rato:

| Ventana | Dirección | Cuenta |
|---|---|---|
| 1 · Normal | http://127.0.0.1:8000 | Turista: `maria@correo.test` |
| 2 · Incógnito (`Ctrl + Shift + N`) | http://127.0.0.1:8000 | Agencia: `info@brisatropical.test`, y después el administrador `admin@explorard.test` |

La contraseña de todas las cuentas de prueba es `password`.
- [ ] Zoom del navegador al 100 % (`Ctrl + 0`) y cerrar las otras pestañas y notificaciones.

---

## 1. El problema (1 minuto)

*«En República Dominicana, la información sobre destinos, excursiones, paquetes y servicios turísticos
está repartida en muchas páginas web y redes sociales. Cada agencia publica por separado.»*

*«Por eso los turistas pierden tiempo buscando y les cuesta comparar opciones, y muchas agencias pequeñas
no tienen cómo darse a conocer.»*

A quién afecta: turistas nacionales y extranjeros, agencias, empresas de excursiones y de renta de vehículos.

## 2. La solución (1 minuto)

*«Explora RD es una plataforma que reúne en un solo lugar los destinos, las excursiones y las agencias del país.
El turista busca, compara y contacta directamente a la agencia, desde la computadora o el celular.»*

Tres tipos de usuario: **turista**, **agencia** y **administrador**, cada uno con su panel.

## 3. Tecnologías (1 minuto)

| Para… | Usé… |
|---|---|
| La página y la lógica | **Laravel 13** (PHP) con vistas **Blade** |
| La base de datos | **MySQL** (con WampServer) |
| El diseño adaptable | **Bootstrap 5**, HTML, CSS y JavaScript |
| El mapa | **Leaflet** con mapas de **OpenStreetMap** |
| El reporte con gráficas | **Python** con **matplotlib** |
| Los idiomas | Localización de Laravel: español, inglés y francés |
| Organizar el trabajo | Visual Studio Code, Git |

---

## 4. Demostración (8 minutos)

### 4.1 Como turista — Ventana 1 (3 minutos)

1. **Portada** (http://127.0.0.1:8000).
   *«La portada sigue el diseño de referencia: foto tropical, título con letra Lora y buscador flotante con pestañas.»*
2. **Buscador:** escribir **Punta Cana**, elegir una fecha → **Buscar**.
   Aparecen el Catamarán a isla Saona y Buggies y playa Macao.
3. Ordenar por **«Mejor valoradas»** y señalar las estrellas ★ de las tarjetas.
4. Tocar el **corazón ♡** de una tarjeta: se pone rojo sin recargar la página.
   *«Así el turista guarda las excursiones que le gustan.»*
5. Abrir el **Catamarán a isla Saona**:
   - Cambiar las personas: el **total estimado** cambia solo.
   - Bajar a **«Opiniones de viajeros»**: el promedio, las barras y los comentarios.
   - Elegir una fecha → **«Solicitar reserva»** → «Mi cuenta» la muestra **Pendiente**.
6. En «Mi cuenta», en **Buggies y playa Macao** (un viaje que María ya hizo): **«Escribir reseña»** → 4 estrellas → **Publicar**.
   *«Solo puede opinar quien ya hizo la excursión. Así las reseñas son reales.»*
7. **Destinos** (menú): mostrar el **mapa**, tocar un punto → «Ver destino» → **Jarabacoa** con su mapa y «Cómo llegar».
8. **Servicios → Renta de vehículos**: los tipos de vehículo y el formulario.
9. **Galería** (pie de página): tocar una foto → pasar con las flechas → mostrar el **crédito del autor**.
10. Cambiar el idioma a **EN** y después a **FR**: *«La página está preparada para turistas de otros países.»* Volver a **ES**.

### 4.2 Como agencia — Ventana 2 (2 minutos)

1. Entrar como `info@brisatropical.test`.
2. **Panel:** las tarjetas de totales, cuántas veces guardaron sus excursiones en favoritos y su calificación promedio.
3. La **campanita 🔔** avisa de la reserva nueva de María → **Confirmar**.
4. En la **Ventana 1** (María), recargar «Mi cuenta»: la reserva ya dice **Confirmada**.
   *«Este es el recorrido completo del diagrama de flujo: el turista solicita, la agencia confirma y el turista lo ve.»*
5. (Si hay tiempo) **Mi perfil**: logo, fotos, videos y WhatsApp → «Ver mi perfil público».

### 4.3 Como administrador — Ventana 2 (2 minutos)

1. Cerrar sesión y entrar como `admin@explorard.test`.
2. **Usuarios y agencias**: se puede suspender una cuenta.
3. **Asesorías y vehículos**: marcar una solicitud como «Atendida» y escribir una respuesta.
4. **Reseñas**: se puede ocultar una reseña ofensiva.
5. **Reporte (Python)** → **«Actualizar reporte»**.
   *«Laravel prepara los datos en un archivo JSON y un programa de Python dibuja las gráficas con matplotlib
   y escribe conclusiones automáticas.»* Mostrar las gráficas y las conclusiones.
   *«La reserva que acabo de confirmar ya aparece en los números.»*

### 4.4 En el celular (1 minuto)

En el navegador: `F12` → icono del celular (`Ctrl + Shift + M`) → elegir un iPhone o Galaxy.
Mostrar el menú ☰ y que nada se sale de la pantalla.
*«No hice una app aparte: la misma página se adapta al celular, la tableta y la computadora.»*

---

## 5. Base de datos (1 minuto)

Mostrar `docs/diagrama_entidad_relacion.png`.

- *«Hay una sola tabla de usuarios con un campo **rol**: turista, agencia o administrador.»*
- *«Las relaciones principales son de uno a muchos: una agencia publica muchas excursiones, un destino tiene muchas excursiones y un turista hace muchas reservas.»*
- *«`favoritos` es una relación de muchos a muchos: un turista guarda muchas excursiones y una excursión la guardan muchos turistas.»*
- *«Las tablas marcadas como NUEVA las agregué durante el desarrollo, para cubrir las asesorías, la renta de vehículos y las reseñas.»*

## 6. Pruebas (1 minuto)

Mostrar en la terminal `php artisan test` (tarda unos 10 segundos).

*«Hay 131 pruebas automáticas en Laravel y 8 en Python. Revisan las páginas, los roles, las reservas, las reseñas,
los idiomas y la seguridad. Además hice un plan de pruebas manuales por cada requerimiento (`PLAN_DE_PRUEBAS.md`).»*

## 7. Cierre (30 segundos)

*«Explora RD resuelve el problema de la información turística repartida: reúne destinos, excursiones y agencias
en un solo lugar, en tres idiomas y para cualquier dispositivo. Está lista para publicarse en internet
(`GUIA_PUBLICACION.md`) y para que una agencia turística la use.»*

*«Muchas gracias. ¿Tienen alguna pregunta?»*

---

## ❓ Preguntas que te pueden hacer

| Pregunta | Respuesta sugerida |
|---|---|
| ¿Por qué Laravel? | Organiza el código en partes (modelos, vistas y controladores), trae seguridad (contraseñas cifradas, protección de formularios) y facilita la base de datos y los idiomas. |
| ¿Cómo sabe la página qué puede hacer cada usuario? | Un **middleware** revisa el **rol** antes de abrir cada panel. Si una agencia intenta entrar al panel del administrador, la devuelve a su panel. |
| ¿Las contraseñas se guardan tal cual? | No. Se guardan **cifradas**: ni el administrador puede verlas. |
| ¿Se puede pagar en la página? | No. Explora RD conecta al turista con la agencia; el pago lo acuerdan entre ellos. Se podría agregar más adelante. |
| ¿Y la aplicación para celular del Paso 1? | La página es **adaptable**: funciona en el celular sin instalar nada. Una app aparte sería una mejora futura. |
| ¿Para qué usaste Python? | Para el **reporte de estadísticas**: lee los datos que prepara Laravel, dibuja 5 gráficas con matplotlib y escribe conclusiones automáticas. |
| ¿Cómo evitas reseñas falsas? | Solo puede opinar quien tiene una reserva **confirmada** cuya fecha ya pasó, una sola vez por excursión. El administrador puede ocultar reseñas. |
| ¿Las fotos son tuyas? | Son de **Unsplash** y **Pexels**, de uso gratuito. Anoté el autor de cada una en `public/img/CREDITOS.txt` y la galería lo muestra. |
| ¿Cómo agregas otro idioma? | Se crea un archivo nuevo en `lang/` con las traducciones. Una prueba revisa que no falte ninguna frase. |
| ¿Qué pasa si se cae internet? | La página funciona en la computadora; solo el mapa necesita internet. |
| ¿Qué harías después? | Publicarla, poner fotos propias de agencias reales, agregar pagos en línea y una app para celular. |

---

## 🆘 Si algo falla durante la presentación

| Problema | Solución rápida |
|---|---|
| La página no abre | Revisa que WampServer esté en verde y que la terminal con `php artisan serve` siga abierta. |
| Error de base de datos | WampServer apagado o en naranja: haz clic en su icono → «Iniciar todos los servicios». |
| El mapa no aparece | No hay internet (o Fortinet lo bloquea): explica que usa OpenStreetMap y sigue con el resto. |
| «Demasiados intentos» al entrar | Escribiste mal la contraseña varias veces: espera 1 minuto. |
| El reporte no se actualiza | Muestra el último reporte que ya estaba creado y explica cómo funciona. |
| Se te olvida un paso | Este guion está en la carpeta del proyecto: `GUION_PRESENTACION.md`. |
