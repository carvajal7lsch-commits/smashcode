param([int]$WebPort = 8097, [string]$WampPath = 'C:\wamp64', [string]$XamppPath = 'C:\xampp')
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot
Set-Location $root
if (!(Test-Path .env)) { throw 'Crea .env siguiendo docs/local.md antes de arrancar.' }
$settings = @{}
Get-Content .env | ForEach-Object {
    if ($_ -match '^([A-Z_]+)=(.*)$') { $settings[$matches[1]] = $matches[2].Trim('"').Trim("'") }
}
if ($settings['APP_ENV'] -ne 'local' -or $settings['DB_HOST'] -ne '127.0.0.1' -or $settings['DB_PUERTO'] -ne '3308' -or $settings['DB_NOMBRE'] -ne 'smash_code') {
    throw 'Este lanzador exige APP_ENV=local, DB_HOST=127.0.0.1, DB_PUERTO=3308 y DB_NOMBRE=smash_code.'
}
if (!(Test-Path vendor/autoload.php)) { throw 'Ejecuta composer install antes de arrancar.' }
$local = Join-Path $root '.local'
$logs = Join-Path $local 'logs'
$sessions = Join-Path $local 'sessions'
New-Item -ItemType Directory -Force $logs,$sessions | Out-Null
$data = $settings['LOCAL_MYSQL_DATADIR']
if (!$data) { $data = Join-Path $local 'mysql' }

# Motor de base de datos: Wamp (MySQL 8) o XAMPP (MariaDB). El primero que exista.
# Ambos arrancan igual; solo cambian el basedir y la forma de inicializar el datadir.
$basedir = $null
if (Test-Path (Join-Path $WampPath 'bin\mysql')) {
    $version = Get-ChildItem (Join-Path $WampPath 'bin\mysql') -Directory | Sort-Object Name -Descending | Select-Object -First 1
    if ($version) { $basedir = $version.FullName }
}
if (!$basedir -and (Test-Path (Join-Path $XamppPath 'mysql\bin\mysqld.exe'))) { $basedir = Join-Path $XamppPath 'mysql' }
if (!$basedir) { throw 'No se encontró MySQL de Wamp ni MariaDB de XAMPP.' }
$mysqlExe = Join-Path $basedir 'bin\mysqld.exe'
$instalador = Join-Path $basedir 'bin\mysql_install_db.exe'
# MariaDB no admite --initialize-insecure ni --mysqlx; trae su propio instalador de datadir.
$esMariaDB = Test-Path $instalador
$phpExe = (Get-Command php).Source
function PortOpen([int]$Port) {
    $socket = [Net.Sockets.TcpClient]::new()
    try { $socket.Connect('127.0.0.1',$Port); return $true } catch { return $false } finally { $socket.Dispose() }
}
$fresh = !(Test-Path (Join-Path $data 'auto.cnf')) -and !(Test-Path (Join-Path $data 'mysql\user.frm')) -and !(Test-Path (Join-Path $data 'mysql\user.MAI'))
if (!(PortOpen 3308)) {
    New-Item -ItemType Directory -Force $data | Out-Null
    if ($fresh) {
        if ($esMariaDB) {
            & $instalador "--datadir=$data" --port=3308 --default-user --silent
        } else {
            & $mysqlExe --no-defaults --initialize-insecure "--basedir=$basedir" "--datadir=$data" --console
        }
        if ($LASTEXITCODE -ne 0) { throw 'No se pudo inicializar MySQL local.' }
    }
    $mysqlArgs = @('--no-defaults', "--basedir=`"$basedir`"", "--datadir=`"$data`"", '--port=3308','--bind-address=127.0.0.1','--console')
    if (!$esMariaDB) { $mysqlArgs += '--mysqlx=OFF' }
    Start-Process -FilePath $mysqlExe -ArgumentList $mysqlArgs -WindowStyle Hidden -RedirectStandardOutput (Join-Path $logs 'mysql.out.log') -RedirectStandardError (Join-Path $logs 'mysql.err.log')
    for ($i=0; $i -lt 30 -and !(PortOpen 3308); $i++) { Start-Sleep -Milliseconds 500 }
    if (!(PortOpen 3308)) { throw 'MySQL no arrancó. Consulta .local/logs/mysql.err.log.' }
}
# Antes de ejecutar migraciones, verificar que el puerto pertenece a esta instancia aislada.
& $phpExe -d xdebug.mode=off tools/verify-local-db.php $data
if ($LASTEXITCODE -ne 0) { throw 'La instancia no coincide; no se ejecuta mantenimiento.' }
& $phpExe -d xdebug.mode=off database/migrar.php
if ($LASTEXITCODE -ne 0) { throw 'Fallaron las migraciones.' }
if ($fresh) {
    & $phpExe -d xdebug.mode=off database/seeds/run_seeds.php
    if ($LASTEXITCODE -ne 0) { throw 'Falló la carga inicial de contenido.' }
    & $phpExe -d xdebug.mode=off database/migrar.php
    if ($LASTEXITCODE -ne 0) { throw 'Falló la migración posterior a seeds.' }
    & $phpExe -d xdebug.mode=off tools/create-local-users.php
    if ($LASTEXITCODE -ne 0) { throw 'No se crearon las cuentas locales.' }
}
if (!(PortOpen $WebPort)) {
    $phpArgs = @('-d','xdebug.mode=off','-d',"session.save_path=`"$sessions`"",'-S',"127.0.0.1:$WebPort",'-t',"`"$root`"", "`"$root\tools\local-router.php`"")
    Start-Process -FilePath $phpExe -ArgumentList $phpArgs -WorkingDirectory $root -WindowStyle Hidden -RedirectStandardOutput (Join-Path $logs 'php.out.log') -RedirectStandardError (Join-Path $logs 'php.err.log')
    for ($i=0; $i -lt 20 -and !(PortOpen $WebPort); $i++) { Start-Sleep -Milliseconds 250 }
}
$response = Invoke-WebRequest "http://127.0.0.1:$WebPort/login" -UseBasicParsing
if ($response.StatusCode -ne 200 -or $response.Headers['X-Smashcode-Local'] -ne 'true') { throw 'El puerto web no sirve este proyecto.' }
Write-Output "SmashCode disponible en http://127.0.0.1:$WebPort (MySQL aislado en 3308)."
