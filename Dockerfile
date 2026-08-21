FROM php:8.2-apache

# Habilitar mod_rewrite para que funcionen las rutas en index.php
RUN a2enmod rewrite

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar la configuración del VirtualHost (si decides usar subdominios o configuraciones especiales)
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Instalar herramientas del sistema necesarias para Composer
RUN apt-get update && apt-get install -y git unzip zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código fuente
COPY . /var/www/html/

# Instalar las dependencias de PHP (crea la carpeta vendor dentro del contenedor)
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

# Entrypoint: aplica las migraciones pendientes y luego arranca Apache.
# Se copia aparte del COPY general para que un cambio en el script no invalide
# la cache de las capas anteriores.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
