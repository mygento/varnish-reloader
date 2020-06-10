FROM mygento/php:7.4

WORKDIR /app

ENTRYPOINT ["php", "index.php"]

COPY --chown=www-data:www-data composer.json /app/composer.json
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts

COPY --chown=www-data:www-data . /app
