#!/usr/bin/env bash
# Crea una base limpia con las tablas y los datos de demo y la exporta a un
# .sql que se puede importar en phpMyAdmin (MySQL 5.7+ o MariaDB 10+).
#
# Uso: bash scripts/exportar-sql.sh deploy/barberia.sql
# Requiere los contenedores en marcha:  docker compose up -d
set -euo pipefail

cd "$(dirname "$0")/.."
DESTINO="${1:-deploy/barberia.sql}"
BASE=barberia_exportar
ROOT_PASS=root_local_only

docker compose exec -T db mysql -uroot -p"$ROOT_PASS" -e \
    "DROP DATABASE IF EXISTS $BASE; CREATE DATABASE $BASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON $BASE.* TO 'barberia'@'%';" 2>/dev/null

docker compose exec -T -e DB_DATABASE=$BASE app php artisan migrate:fresh --seed --force >/dev/null

mkdir -p "$(dirname "$DESTINO")"
docker compose exec -T db mysqldump -uroot -p"$ROOT_PASS" \
    --no-tablespaces --skip-comments --set-gtid-purged=OFF --single-transaction \
    --ignore-table=$BASE.sessions --ignore-table=$BASE.cache --ignore-table=$BASE.cache_locks \
    $BASE 2>/dev/null > "$DESTINO.tmp"

# Tablas vacías que la app necesita (sesiones y caché), sin datos.
docker compose exec -T db mysqldump -uroot -p"$ROOT_PASS" \
    --no-tablespaces --skip-comments --set-gtid-purged=OFF --no-data \
    $BASE sessions cache cache_locks 2>/dev/null >> "$DESTINO.tmp"

# Compatibilidad con MariaDB / MySQL antiguos del hosting compartido.
sed -E \
    -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' \
    -e '/^\/\*!80[0-9]{3} .*\*\/;?$/d' \
    "$DESTINO.tmp" > "$DESTINO"
rm -f "$DESTINO.tmp"

docker compose exec -T db mysql -uroot -p"$ROOT_PASS" -e "DROP DATABASE $BASE;" 2>/dev/null
