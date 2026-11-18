#!/usr/bin/env bash
# Arma el paquete para subir a un hosting compartido sin terminal (InfinityFree).
#
# Resultado en deploy/:
#   barberia-htdocs.zip  -> se sube y descomprime dentro de htdocs/
#   barberia.sql         -> se importa en phpMyAdmin
#
# Estructura dentro de htdocs/:
#   index.php, .htaccess, build/   (lo público)
#   laravel/                       (la aplicación, bloqueada al público)
#
# Requiere Docker y Node.js. Uso:  bash scripts/empaquetar-infinityfree.sh
set -euo pipefail

cd "$(dirname "$0")/.."
RAIZ="$(pwd)"
SALIDA="$RAIZ/deploy"
HTDOCS="$SALIDA/htdocs"
APP="$HTDOCS/laravel"

# En Git Bash de Windows, Docker necesita rutas tipo C:/...
ruta_docker() { (cd "$1" && (pwd -W 2>/dev/null || pwd)); }

echo "› Limpiando deploy/"
rm -rf "$SALIDA"
mkdir -p "$APP"

echo "› Compilando CSS y JS"
npm run build --silent

echo "› Copiando la aplicación"
cp -r app bootstrap config database routes artisan composer.json composer.lock "$APP/"
mkdir -p "$APP/resources"
cp -r resources/views "$APP/resources/"
mkdir -p "$APP/storage/app/public" "$APP/storage/logs" \
         "$APP/storage/framework/cache/data" "$APP/storage/framework/sessions" "$APP/storage/framework/views"
rm -f "$APP"/bootstrap/cache/*.php
# Un .zip no guarda carpetas vacías: este archivo hace que existan al descomprimir.
for d in storage/app/public storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache; do
    touch "$APP/$d/.mantener"
done

echo "› Instalando dependencias de producción (sin las de desarrollo)"
MSYS_NO_PATHCONV=1 docker run --rm -v "$(ruta_docker "$APP"):/app" -w /app composer:2 \
    install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-req=ext-* --quiet

echo "› Copiando la parte pública"
cp -r public/build public/favicon.ico public/robots.txt public/.htaccess "$HTDOCS/"
cat > "$HTDOCS/index.php" <<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/laravel/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

// La carpeta pública es htdocs/ (donde está este archivo), no laravel/public.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
PHP

# Nadie puede abrir desde el navegador los archivos de la aplicación (.env incluido).
cat > "$APP/.htaccess" <<'HT'
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
HT

echo "› Creando .env de producción"
CLAVE="base64:$(head -c 32 /dev/urandom | base64)"
cat > "$APP/.env" <<ENV
APP_NAME="Barbería Navaja"
APP_ENV=production
APP_KEY=$CLAVE
APP_DEBUG=false
# Cambia por tu dirección. Usa https:// solo cuando el certificado SSL esté activo.
APP_URL=https://CAMBIAR.infinityfreeapp.com
APP_TIMEZONE=America/Lima
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

LOG_CHANNEL=single
LOG_LEVEL=error

# Datos que te da InfinityFree en "MySQL Databases".
DB_CONNECTION=mysql
DB_HOST=CAMBIAR
DB_PORT=3306
DB_DATABASE=CAMBIAR
DB_USERNAME=CAMBIAR
DB_PASSWORD=CAMBIAR

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=sync

BARBERIA_NOMBRE="Barbería Navaja"
BARBERIA_WHATSAPP=51900000000
ENV

echo "› Exportando la base de datos con datos de demo"
bash "$RAIZ/scripts/exportar-sql.sh" "$SALIDA/barberia.sql"

echo "› Comprimiendo"
python - "$HTDOCS" "$SALIDA/barberia-htdocs.zip" <<'PY'
import os, sys, zipfile
origen, destino = sys.argv[1], sys.argv[2]
with zipfile.ZipFile(destino, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
    for carpeta, _, archivos in os.walk(origen):
        for nombre in archivos:
            ruta = os.path.join(carpeta, nombre)
            z.write(ruta, os.path.relpath(ruta, origen).replace(os.sep, '/'))
PY

ARCHIVOS=$(find "$HTDOCS" -type f | wc -l)
echo
echo "Listo:"
echo "  deploy/barberia-htdocs.zip  ($(du -h "$SALIDA/barberia-htdocs.zip" | cut -f1), $ARCHIVOS archivos)"
echo "  deploy/barberia.sql         ($(du -h "$SALIDA/barberia.sql" | cut -f1))"
