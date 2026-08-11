# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos las extensiones de MySQL necesarias para PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitamos mod_rewrite de Apache (muy útil si luego usas .htaccess)
RUN a2enmod rewrite

# Copiamos todo tu proyecto a la carpeta pública de Apache
COPY . /var/www/html/

# Damos los permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Exponemos el puerto 80
EXPOSE 80