# 🌐 Publicar Explora RD gratis en InfinityFree

Guía paso a paso para subir Explora RD a **InfinityFree** (infinityfree.com) con un **subdominio gratis**
(por ejemplo `explorard.infinityfreeapp.com`). Sirve también para otros hostings gratuitos **sin terminal**.

> Para una agencia de verdad conviene un hosting de pago con cPanel y SSH: ver [`GUIA_PUBLICACION.md`](GUIA_PUBLICACION.md).

---

## ¿Qué funciona y qué no?

| Funciona ✅ | No funciona o hay que probar ⚠️ |
|---|---|
| Toda la página pública, idiomas, mapa y galería | **Reporte de Python**: el hosting no deja ejecutar Python (el botón muestra un aviso) |
| Registro, inicio de sesión y roles | **Correos** (recuperar contraseña, avisos): los hostings gratis no envían correos |
| Reservas, reseñas, favoritos, asesorías y renta | **Asistente con IA**: hay que probar si el hosting deja conectarse a Anthropic (si no, el chat ofrece WhatsApp) |
| Paneles de agencia y de administrador, fotos de agencias | No hay terminal: nada de `php artisan` ni `composer` (por eso el paquete va ya preparado) |

---

## Paso 0 · Preparar el paquete (en tu computadora)

Con WampServer en **verde**, en la carpeta del proyecto:

```
powershell -ExecutionPolicy Bypass -File scripts\preparar-infinityfree.ps1
```

Crea la carpeta **`publicar\`** con:
- `explora-rd\` → los archivos que vas a subir (con `vendor` sin las herramientas de pruebas, un `.htaccess` especial y un `.env` de producción con una clave secreta nueva).
- `explora-rd-infinityfree.zip` → lo mismo, comprimido.
- `base_de_datos.sql` → las tablas y los **12 destinos** (con traducciones y mapa). **Sin** cuentas ni excursiones de prueba.

⚠️ `publicar\` tiene contraseñas cuando la completes: no la compartas ni la subas a GitHub (ya está en `.gitignore`).

---

## Paso 1 · Crear la cuenta y el subdominio (lo haces tú)

1. Entra a **infinityfree.com** → *Register* y crea tu cuenta.
2. *Create Account* (cuenta de hosting) → elige **subdominio gratis**, por ejemplo `explorard` + `.infinityfreeapp.com`.
3. Abre el **panel de control** (Control Panel) de esa cuenta.
4. Busca la **versión de PHP** (*PHP Config* / *Alter PHP Config*): tiene que ser **8.3 o más reciente**. Si se puede elegir, elige la más nueva.
   Si solo hay versiones menores que 8.3, Laravel 13 no funcionará ahí: avísame.

## Paso 2 · Crear la base de datos

1. En el panel: **MySQL Databases** → crea una base llamada `explora`.
2. Anota (aparecen en esa misma pantalla):
   - **MySQL Host Name** → algo como `sql123.infinityfree.com`
   - **MySQL DB Name** → algo como `if0_12345678_explora`
   - **MySQL User Name** → algo como `if0_12345678`
   - **MySQL Password** → la contraseña de tu cuenta de hosting (en *Account Details*). 🔒 No la compartas con nadie.

## Paso 3 · Cargar las tablas y los destinos

1. En *MySQL Databases*, al lado de tu base, pulsa **phpMyAdmin**.
2. Pestaña **Importar** → *Seleccionar archivo* → `publicar\base_de_datos.sql` → **Continuar**.
3. A la izquierda deben aparecer las tablas (`destinos`, `excursiones`, `usuarios`, `reservas`...). Abre `destinos`: debe tener **12 filas**.

## Paso 4 · Completar el archivo `.env`

Abre `publicar\explora-rd\.env` con el Bloc de notas o VS Code y cambia lo que dice **CAMBIAR**:

```
APP_URL=https://explorard.infinityfreeapp.com      ← tu subdominio
DB_HOST=sql123.infinityfree.com                     ← MySQL Host Name
DB_DATABASE=if0_12345678_explora                    ← MySQL DB Name
DB_USERNAME=if0_12345678                            ← MySQL User Name
DB_PASSWORD=la-contraseña-de-tu-cuenta              ← MySQL Password
```

Opcional: cambia `EXPLORA_WHATSAPP`, `EXPLORA_CORREO` y `EXPLORA_TELEFONO` por los datos reales de contacto,
y pon tu clave en `ANTHROPIC_API_KEY` si quieres probar el asistente con IA.

## Paso 5 · Subir los archivos a `htdocs`

**Recomendado: FileZilla** (programa gratis para FTP; descárgalo de **filezilla-project.org**).

1. En el panel de InfinityFree: **FTP Details** → anota *FTP Hostname*, *FTP Username* y *FTP Password*.
2. En FileZilla escribe esos datos arriba (Puerto `21`) → **Conexión rápida**.
3. Menú **Servidor → Forzar mostrar archivos ocultos** (para que se suban `.htaccess` y `.env`).
4. En el lado derecho (el servidor), entra a la carpeta **`htdocs`** y borra el archivo de bienvenida que trae (por ejemplo `index2.html`).
5. En el lado izquierdo, entra a `publicar\explora-rd`, selecciona **todo su contenido** (no la carpeta, lo de adentro) y arrástralo a `htdocs`.
6. Espera: son unos **6,600 archivos** y puede tardar **30 a 60 minutos**. Si alguno falla, FileZilla lo reintenta (pestaña *Transferencias fallidas* → clic derecho → *Restablecer y volver a encolar*).

Al terminar, `htdocs` debe tener: `.htaccess`, `.env`, `app`, `bootstrap`, `config`, `database`, `lang`, `public`, `python`, `resources`, `routes`, `storage`, `vendor`, `artisan`, `composer.json`...

## Paso 6 · Abrir la página y activar el candado (SSL)

1. Abre tu subdominio: debe salir la portada «Descubre República Dominicana».
   (Los subdominios nuevos pueden tardar unos minutos en funcionar.)
2. En el área de clientes de InfinityFree: **SSL Certificates** → pide el certificado gratis para tu subdominio y sigue sus pasos.
3. Cuando la página abra con **https** y candado, cambia en el `.env` del servidor (con FileZilla: clic derecho → *Ver/Editar*):
   `SESSION_SECURE_COOKIE=true`

## Paso 7 · Crear tu cuenta de administrador

No hay terminal para `php artisan explora:crear-admin`, así que se hace así:
1. En tu página publicada: **Entrar → Crear cuenta** como **turista**, con tu correo y una **contraseña segura**.
2. En **phpMyAdmin** → tabla `usuarios` → busca tu fila → **Editar** → en `rol` escribe `administrador` → **Continuar**.
   (O pestaña **SQL**: `UPDATE usuarios SET rol='administrador' WHERE correo='tu-correo@ejemplo.com';`)
3. Cierra sesión y vuelve a entrar: ya verás el **Panel del administrador**.

## Paso 8 · Revisar que todo funcione

- [ ] La portada, **Destinos** (con el mapa) y **Galería** se ven bien.
- [ ] Puedes registrar una **agencia**, subir su **logo** y publicar una **excursión**.
- [ ] Con una cuenta de **turista** puedes pedir una **reserva** y la agencia la ve en su campanita.
- [ ] Cambiar el idioma a **EN** y **FR** funciona.
- [ ] Abrir `https://tu-subdominio/.env` muestra «Página no encontrada» (el archivo secreto está protegido).

---

## 🆘 Problemas frecuentes

| Problema | Qué hacer |
|---|---|
| Página en blanco o «500» | Revisa la versión de PHP (8.3+) y que `.env` y `vendor` se hayan subido completos. Mira `storage/logs/` en FileZilla. |
| «Error al conectar con la base de datos» | Revisa `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en el `.env` del servidor. En InfinityFree el host **no** es `127.0.0.1`. |
| La página sale sin colores ni fotos | Revisa `APP_URL` en `.env` (con tu subdominio exacto). |
| No puedo iniciar sesión (vuelve a pedir entrar) | Si la página todavía abre con `http`, pon `SESSION_SECURE_COOKIE=false`. |
| Se ve «This site requires JavaScript» un momento | Es la protección de InfinityFree contra robots; con un navegador normal pasa sola. |
| Cambié algo en el proyecto | Vuelve a ejecutar el script del Paso 0 y sube solo las carpetas que cambiaron (por ejemplo `resources`, `public/css`). **No** vuelvas a importar `base_de_datos.sql` (borraría las tablas con los datos nuevos). |

> Probado en la computadora: el paquete se ejecutó en modo producción con la base importada desde `base_de_datos.sql`
> (registro, panel de agencia, subida de logo a `public/storage`, administrador creado con phpMyAdmin y las páginas públicas).
> La prueba se hizo con MySQL 8.4; InfinityFree usa MariaDB, que es compatible con este archivo.
