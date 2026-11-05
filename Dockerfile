FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql && a2enmod rewrite

COPY docker/apache-gridwatch.conf /etc/apache2/conf-enabled/gridwatch.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Standaard-inhoud; docker-compose.yml overschrijft dit met ./web zodat wijzigingen direct zichtbaar zijn.
COPY web/ /var/www/html/

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
