# On part d'une image PHP avec Apache intégré
FROM php:8.3-apache

# Installation des extensions PHP nécessaires (ex: pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql

# On active les modules Apache utiles pour rewrite, compression et cache.
RUN a2enmod rewrite headers expires deflate

# Autorise l'utilisation de .htaccess pour l'URL rewriting.
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# On définit le dossier de travail dans le container
WORKDIR /var/www/html

# On expose le port 80
EXPOSE 80