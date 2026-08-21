#!/bin/sh
#
# docker-entrypoint.sh
# Deja la base de datos al dia antes de arrancar Apache, para que levantar el
# stack no exija correr migraciones a mano en ningun entorno.
#
# Las migraciones son idempotentes, asi que aplicarlas en cada arranque es
# seguro: las que ya estaban simplemente responden "no se hace nada".
set -e

echo "[entrypoint] Aplicando migraciones de base de datos..."
php /var/www/html/database/migrar.php

echo "[entrypoint] Migraciones al dia. Arrancando Apache..."
exec "$@"
