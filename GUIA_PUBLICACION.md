# 🚀 Guía para publicar Explora RD en internet

WampServer sirve **solo para trabajar en tu computadora**. Para que cualquier persona entre a Explora RD desde su celular hace falta un **hosting** (un servidor en internet) y un **dominio** (la dirección, por ejemplo `explorard.com` o `explorard.com.do`).

Esta guía explica los pasos. Algunos (contratar el hosting, comprar el dominio, crear la cuenta de GitHub) los tiene que hacer una persona adulta responsable o la agencia que compre el proyecto, porque llevan pagos y datos personales.

---

## 1. Antes de empezar (en tu computadora)

### 1.1 Instalar Git y subir el proyecto a GitHub
Git guarda el historial de cambios y GitHub lo guarda en internet (así el proyecto no se pierde si se daña la computadora).

1. Descarga Git desde **https://git-scm.com** e instálalo (deja las opciones que vienen marcadas).
2. Crea una cuenta en **https://github.com** y un repositorio **privado** llamado `explora-rd`.
3. En la terminal de Visual Studio Code, dentro de la carpeta del proyecto:
   ```bash
   git init
   git add .
   git commit -m "Explora RD: etapas 1 a 8"
   git branch -M main
   git remote add origin https://github.com/TU-USUARIO/explora-rd.git
   git push -u origin main
   ```
4. El archivo `.gitignore` ya evita que se suban cosas privadas o pesadas: `.env` (contraseñas), `vendor/` y las fotos que suben las agencias.

### 1.2 Revisar que todo pase las pruebas
```bash
php artisan test
```
Deben pasar las **146 pruebas** (y las 8 de Python: `python -m unittest discover -s python`). Si alguna falla, **no publiques** hasta corregirla.

---

## 2. Elegir el hosting y el dominio

El hosting debe tener:

| Requisito | Por qué |
|---|---|
| **PHP 8.3 o más reciente** | Laravel 13 lo necesita |
| Extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` (e `intl`, recomendada) | Base de datos, textos con tildes, seguridad y fotos |
| **MySQL** (o MariaDB) | Para la base de datos |
| **Acceso SSH** y **Composer** | Para ejecutar los comandos de Laravel |
| **Certificado SSL gratis** (Let's Encrypt) | Para el candado 🔒 y `https://` |
| Poder cambiar la **carpeta pública** (document root) | Laravel debe mostrar solo la carpeta `public/` |

Sirve un hosting compartido con cPanel que cumpla esa lista, o un servidor VPS. El dominio `.com.do` se compra en **NIC.DO**; los `.com` en el mismo hosting.

---

## 3. Subir el proyecto al servidor

**Opción A (recomendada, con SSH):**
```bash
cd ~
git clone https://github.com/TU-USUARIO/explora-rd.git
cd explora-rd
composer install --no-dev --optimize-autoloader
```

**Opción B (sin SSH):** en tu computadora ejecuta `composer install --no-dev`, comprime la carpeta del proyecto **sin** `.env`, `node_modules` ni `storage/logs`, súbela con el administrador de archivos del hosting y descomprímela.

> ⚠️ La **carpeta pública** del dominio debe apuntar a `explora-rd/public`, **no** a `explora-rd`. Si no se hace así, cualquiera podría ver el archivo `.env` con las contraseñas.

---

## 4. Configurar

1. En cPanel crea una **base de datos MySQL**, un **usuario** con contraseña segura y dale todos los permisos sobre la base.
2. Copia `.env.produccion.ejemplo` con el nombre `.env` y cambia todo lo que dice **CAMBIAR**:
   - `APP_URL` → tu dominio con `https://`
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` → los de la base que creaste
   - `MAIL_...` → los datos del correo SMTP del hosting
   - `EXPLORA_WHATSAPP`, `EXPLORA_CORREO`, `EXPLORA_TELEFONO` → los reales
   - Revisa que diga `APP_DEBUG=false` (si está en `true`, los visitantes verían detalles técnicos de los errores)
3. Ejecuta, en este orden:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=ProduccionSeeder --force
   php artisan explora:crear-admin
   php artisan storage:link
   php artisan optimize
   ```
   - `ProduccionSeeder` carga **solo los 12 destinos** (con sus traducciones y su ubicación en el mapa). No crea las cuentas de prueba (con contraseña «password») ni excursiones inventadas.
   - `explora:crear-admin` te pregunta el nombre, el correo y una contraseña segura (mínimo 10 caracteres) para el administrador real.
4. Las carpetas `storage/` y `bootstrap/cache/` deben tener permiso de escritura (en cPanel: permisos `775`).
5. Activa el **SSL** en cPanel (sección «SSL/TLS Status» o «Let's Encrypt»).

---

## 5. Revisión final antes de anunciarla

- [ ] La página abre con **https://** y se ve el candado 🔒.
- [ ] `APP_DEBUG=false` (abre una dirección que no existe: debe salir la página «Página no encontrada» de Explora RD, sin detalles técnicos).
- [ ] El botón flotante de WhatsApp abre el número **real**.
- [ ] Crear una cuenta de turista, solicitar una reserva y ver que llega el **correo** a la agencia.
- [ ] Probar «¿Olvidaste tu contraseña?» y ver que llega el correo.
- [ ] Una agencia puede subir su logo y fotos (revisa que se vean).
- [ ] Revisar la página en un **celular** de verdad.
- [ ] En `public/robots.txt` cambia la línea del `Sitemap` por tu dominio y quítale el `#`.
- [ ] Registrar el sitio en **Google Search Console** y enviar `https://tu-dominio.com/sitemap.xml`.

---

## 6. Cuando hagas cambios después

En tu computadora: haz el cambio, corre `php artisan test` y súbelo con `git push`. En el servidor:
```bash
cd ~/explora-rd
php artisan down          # muestra «Volvemos enseguida» mientras actualizas
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan up
```

## 7. Copias de seguridad

- **Cada semana:** en phpMyAdmin → la base de datos → **Exportar** (guarda el archivo `.sql`).
- **Cada mes:** descarga la carpeta `storage/app/public` (las fotos y logos que suben las agencias).
- Guarda las copias fuera del servidor (por ejemplo, en Google Drive).

## 8. El reporte de Python en el servidor

El reporte de estadísticas (panel del administrador → «Reporte (Python)») necesita **Python 3.10+ con matplotlib** en el servidor.

- Muchos hostings compartidos (cPanel) tienen Python: pruébalo en la terminal con `python3 --version`.
- Instala matplotlib para tu usuario: `python3 -m pip install --user matplotlib`
- En el `.env` del servidor escribe `EXPLORA_PYTHON=python3`.
- Si el hosting **no** tiene Python, el resto de la página funciona igual; solo el botón «Generar reporte» muestra un aviso.

## 9. El asistente con IA en el servidor

- Instala el paquete en tu computadora (`composer require anthropic-ai/sdk`) antes de subir el proyecto, para que quede en `composer.json`.
- En el `.env` del servidor escribe `ANTHROPIC_API_KEY=` con la clave de console.anthropic.com. **Nunca** la subas a GitHub.
- En console.anthropic.com puedes poner un **límite de gasto mensual** para no llevarte sorpresas.
- Sin clave, el chat solo ofrece WhatsApp y el resto de la página funciona igual.
