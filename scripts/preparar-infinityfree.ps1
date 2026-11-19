# =====================================================================
#  EXPLORA RD — Prepara el paquete para subir a InfinityFree
#  (o a cualquier hosting gratuito SIN terminal: sin SSH ni Composer)
#
#  Uso (en la carpeta del proyecto, con WampServer en verde):
#     powershell -ExecutionPolicy Bypass -File scripts\preparar-infinityfree.ps1
#
#  Crea la carpeta  publicar\  con:
#     explora-rd\                     lo que se sube a htdocs (archivos listos)
#     explora-rd-infinityfree.zip     lo mismo, comprimido
#     base_de_datos.sql               las tablas y los 12 destinos, para phpMyAdmin
#
#  No cambia nada del proyecto: trabaja sobre una copia.
# =====================================================================

$ErrorActionPreference = 'Stop'
$proyecto = (Resolve-Path "$PSScriptRoot\..").Path
$publicar = Join-Path $proyecto 'publicar'
$destino  = Join-Path $publicar 'explora-rd'

Write-Host "`n1/5 Copiando el proyecto (sin pruebas, documentos ni datos de la computadora)..." -ForegroundColor Cyan
if (Test-Path $destino) { Remove-Item $destino -Recurse -Force }
New-Item -ItemType Directory -Force $destino | Out-Null

# Carpetas que NO se suben
$sinCarpetas = @('.git', '.claude', 'node_modules', 'tests', 'publicar', 'scripts', 'docs', 'documentacion',
                 'referencias', '__pycache__', "$proyecto\public\storage", "$proyecto\storage\app\reportes",
                 "$proyecto\storage\logs", "$proyecto\storage\framework\sessions", "$proyecto\storage\framework\views",
                 "$proyecto\storage\framework\cache", "$proyecto\storage\app\public", "$proyecto\storage\app\private")
# Archivos que NO se suben (el .env de la computadora tiene datos locales)
$sinArchivos = @('.env', '.env.backup', '.phpunit.result.cache', '*.log', 'database.sqlite', '*.md', 'phpunit.xml',
                 'vite.config.js', 'package.json', '.editorconfig', '.gitattributes')
robocopy $proyecto $destino /E /NFL /NDL /NJH /NJS /NP /XD $sinCarpetas /XF $sinArchivos | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy falló (código $LASTEXITCODE)" }

# Carpetas vacías que Laravel necesita para funcionar
foreach ($c in 'storage\logs', 'storage\framework\sessions', 'storage\framework\views', 'storage\framework\cache\data',
               'storage\app\public', 'storage\app\private', 'storage\app\reportes', 'public\storage') {
    New-Item -ItemType Directory -Force (Join-Path $destino $c) | Out-Null
}
Remove-Item (Join-Path $destino 'bootstrap\cache\*.php') -Force -ErrorAction SilentlyContinue

Write-Host "2/5 Quitando de vendor los paquetes que solo sirven para programar (pruebas)..." -ForegroundColor Cyan
Push-Location $destino
try {
    # No descarga nada: solo quita los paquetes de desarrollo que ya están en vendor
    composer install --no-dev --optimize-autoloader --no-interaction --quiet
    if ($LASTEXITCODE -ne 0) { throw 'composer install --no-dev falló' }
} finally { Pop-Location }

Write-Host "3/5 Creando .htaccess y el .env de producción..." -ForegroundColor Cyan
# En InfinityFree todo va dentro de htdocs y no se puede elegir la carpeta «public»:
# este .htaccess manda cada visita a public/ (así .env, app/, vendor/... no se pueden abrir desde internet)
@'
# Explora RD: todo pasa por la carpeta public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Por si acaso: nunca mostrar archivos que empiezan por punto (.env, .git...)
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
'@ | Set-Content -Path (Join-Path $destino '.htaccess') -Encoding ascii

# Clave secreta nueva para la página publicada (distinta de la de la computadora)
$clave = 'base64:' + [Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }) -as [byte[]])
$env = @"
# =====================================================================
#  EXPLORA RD — Configuración de la página publicada en InfinityFree
#  Cambia lo que dice CAMBIAR (ver GUIA_INFINITYFREE.md).
#  Este archivo tiene contraseñas: NUNCA lo compartas ni lo subas a GitHub.
# =====================================================================
APP_NAME="Explora RD"
APP_ENV=production
APP_KEY=$clave
APP_DEBUG=false
APP_URL=https://CAMBIAR.infinityfreeapp.com
APP_TIMEZONE=America/Santo_Domingo
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=7

# Base de datos: los datos están en el panel de InfinityFree, sección «MySQL Databases»
DB_CONNECTION=mysql
DB_HOST=CAMBIAR_sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=CAMBIAR_if0_XXXXXXXX_explora
DB_USERNAME=CAMBIAR_if0_XXXXXXXX
DB_PASSWORD=CAMBIAR_la_contrasena_de_la_cuenta

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
# Ponlo en true cuando la página abra con candado (https)
SESSION_SECURE_COOKIE=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

# Sin terminal no existe «storage:link»: las fotos se guardan directo en public/storage
FOTOS_EN_PUBLIC=true

# Los hostings gratuitos no envían correos: se anotan en storage/logs
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-responder@explorard.test"
MAIL_FROM_NAME="Explora RD"

# Contacto general de Explora RD (sale en la página y en el botón de WhatsApp)
EXPLORA_WHATSAPP=18095550000
EXPLORA_CORREO=info@explorard.test
EXPLORA_TELEFONO=809-555-0000
EXPLORA_DIRECCION="Santo Domingo, República Dominicana"

# Asistente con IA: pon aquí tu clave de console.anthropic.com (si no, el chat ofrece WhatsApp)
ANTHROPIC_API_KEY=
EXPLORA_IA_MODELO=claude-opus-5-5
"@
[IO.File]::WriteAllText((Join-Path $destino '.env'), $env, [Text.UTF8Encoding]::new($false))

Write-Host "4/5 Preparando la base de datos (tablas + los 12 destinos)..." -ForegroundColor Cyan
$mysqlBin = (Get-ChildItem 'C:\wamp64\bin\mysql' -Directory | Sort-Object Name -Descending | Select-Object -First 1).FullName + '\bin'
$baseTemporal = 'explora_rd_publicar'
& "$mysqlBin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS $baseTemporal; CREATE DATABASE $baseTemporal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($LASTEXITCODE -ne 0) { throw '¿Está WampServer en verde? No se pudo crear la base temporal.' }
$env:DB_DATABASE = $baseTemporal
try {
    Push-Location $proyecto
    php artisan migrate --force --seed --seeder=ProduccionSeeder --no-interaction | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo preparar la base de datos.' }
} finally {
    Pop-Location
    Remove-Item Env:\DB_DATABASE
}
$sql = Join-Path $publicar 'base_de_datos.sql'
& "$mysqlBin\mysqldump.exe" -u root --no-tablespaces --set-gtid-purged=OFF --skip-comments --default-character-set=utf8mb4 --result-file="$sql" $baseTemporal
if ($LASTEXITCODE -ne 0) { throw 'mysqldump falló.' }
& "$mysqlBin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS $baseTemporal;"   # se borra la base temporal
# Compatibilidad con MariaDB (la que suelen usar los hostings gratuitos)
$texto = [IO.File]::ReadAllText($sql)
$texto = $texto -replace "/\*!80016 DEFAULT ENCRYPTION='N' \*/", '' -replace 'utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci'
[IO.File]::WriteAllText($sql, "-- Explora RD: tablas y los 12 destinos. Importar en phpMyAdmin (pestaña «Importar»).`n" + $texto, [Text.UTF8Encoding]::new($false))

Write-Host "5/5 Comprimiendo..." -ForegroundColor Cyan
$zip = Join-Path $publicar 'explora-rd-infinityfree.zip'
if (Test-Path $zip) { Remove-Item $zip -Force }
Compress-Archive -Path "$destino\*", "$destino\.htaccess", "$destino\.env" -DestinationPath $zip -CompressionLevel Optimal

$archivos = (Get-ChildItem $destino -Recurse -File -Force | Measure-Object).Count
Write-Host "`nListo: $archivos archivos en publicar\explora-rd" -ForegroundColor Green
Write-Host "Zip:   publicar\explora-rd-infinityfree.zip ($([math]::Round((Get-Item $zip).Length/1MB,1)) MB)" -ForegroundColor Green
Write-Host "SQL:   publicar\base_de_datos.sql" -ForegroundColor Green
Write-Host "Sigue la guía GUIA_INFINITYFREE.md`n"
