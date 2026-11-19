# =====================================================================
#  EXPLORA RD — Sube la carpeta publicar\explora-rd al hosting por FTP
#
#  Uso (en la carpeta del proyecto):
#     powershell -ExecutionPolicy Bypass -File scripts\subir-ftp.ps1 -Servidor ftpupload.net -Usuario if0_12345678
#
#  - El servidor y el usuario están en el panel de InfinityFree, sección «FTP Details».
#  - La contraseña te la pide el script (no se ve al escribirla y no se guarda en ningún lado).
#  - Sube todo dentro de la carpeta htdocs, creando las carpetas que falten.
#  - Usa curl, que ya viene con Windows: no hay que instalar nada.
# =====================================================================
param(
    [Parameter(Mandatory = $true)][string]$Servidor,
    [Parameter(Mandatory = $true)][string]$Usuario,
    [string]$Carpeta = 'htdocs',
    # Para subir solo algunas carpetas después de un cambio, por ejemplo: -Solo resources,public
    [string[]]$Solo = @()
)

$ErrorActionPreference = 'Stop'
$origen = (Resolve-Path "$PSScriptRoot\..\publicar\explora-rd").Path
if (-not (Test-Path "$origen\.env")) { throw 'No existe publicar\explora-rd. Primero ejecuta scripts\preparar-infinityfree.ps1' }
if (Select-String -Path "$origen\.env" -Pattern 'CAMBIAR' -Quiet) {
    Write-Host 'Atención: el archivo publicar\explora-rd\.env todavía tiene datos con «CAMBIAR».' -ForegroundColor Yellow
    if ((Read-Host '¿Subir de todos modos? (s/n)') -ne 's') { return }
}

# La contraseña la escribes tú; no se muestra en la pantalla
$segura = Read-Host "Contraseña del FTP de $Usuario" -AsSecureString
$clave = [Runtime.InteropServices.Marshal]::PtrToStringBSTR([Runtime.InteropServices.Marshal]::SecureStringToBSTR($segura))

# Lista de archivos a subir
$archivos = Get-ChildItem $origen -Recurse -File -Force
if ($Solo.Count) {
    $archivos = $archivos | Where-Object { $rel = $_.FullName.Substring($origen.Length + 1); $Solo | Where-Object { $rel -like "$_*" } }
}
Write-Host "Archivos a subir: $($archivos.Count)" -ForegroundColor Cyan

# curl sube todos los archivos con UNA sola conexión, leyendo la lista de un archivo temporal.
# El archivo temporal incluye la contraseña, así que se borra al terminar (pase lo que pase).
$config = [IO.Path]::Combine([IO.Path]::GetTempPath(), 'explora-ftp-' + [guid]::NewGuid() + '.txt')
$escapar = { param($t) $t.Replace('\', '\\').Replace('"', '\"') }
try {
    $lineas = New-Object System.Collections.Generic.List[string]
    $lineas.Add('user = "' + (& $escapar "${Usuario}:$clave") + '"')
    $lineas.Add('ftp-create-dirs')
    $lineas.Add('retry = 3')
    $lineas.Add('silent')
    $lineas.Add('show-error')
    foreach ($a in $archivos) {
        $rel = $a.FullName.Substring($origen.Length + 1).Replace('\', '/')
        $ruta = (($rel -split '/') | ForEach-Object { [Uri]::EscapeDataString($_) }) -join '/'
        $url = "ftp://$Servidor/$Carpeta/$ruta"
        $lineas.Add('upload-file = "' + (& $escapar $a.FullName) + '"')
        $lineas.Add('url = "' + $url + '"')
    }
    [IO.File]::WriteAllLines($config, $lineas, [Text.UTF8Encoding]::new($false))
    $clave = $null

    Write-Host 'Subiendo... (puede tardar bastante; no cierres esta ventana)' -ForegroundColor Cyan
    $inicio = Get-Date
    curl.exe --config $config
    $codigo = $LASTEXITCODE
} finally {
    Remove-Item $config -Force -ErrorAction SilentlyContinue   # se borra la lista con la contraseña
}

$minutos = [math]::Round(((Get-Date) - $inicio).TotalMinutes, 1)
if ($codigo -eq 0) {
    Write-Host "`nListo: se subieron $($archivos.Count) archivos en $minutos minutos." -ForegroundColor Green
} else {
    Write-Host "`ncurl terminó con el código $codigo. Si arriba ves errores de algunos archivos, vuelve a ejecutar el script (lo que ya se subió se sobrescribe sin problema)." -ForegroundColor Yellow
}
