#!/bin/sh
#
# docker-entrypoint.sh
# Deja la base de datos al dia antes de arrancar Apache, para que levantar el
# stack no exija correr migraciones a mano en ningun entorno.
#
# Las migraciones son idempotentes, asi que aplicarlas en cada arranque es
# seguro: las que ya estaban simplemente responden "no se hace nada".
set -e

# El volumen de subidas se monta vacío y con dueño root: Apache (www-data) no
# podría guardar los audios ni las imágenes que se suben desde el panel.
mkdir -p /var/www/html/assets/uploads/audios /var/www/html/assets/uploads/imagenes
chown -R www-data:www-data /var/www/html/assets/uploads

echo "[entrypoint] Aplicando migraciones de base de datos..."
php /var/www/html/database/migrar.php

echo "[entrypoint] Migraciones al dia. Arrancando Apache..."
exec "$@"
