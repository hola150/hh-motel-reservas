FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Sin --no-dev: el seeder de arranque (DatabaseSeeder -> UserFactory) usa
# fake() de fakerphp/faker, que es una dependencia de desarrollo. Para una
# imagen de producción "de verdad" habría que sacar el seeder de la
# dependencia de Faker y volver a --no-dev.
RUN composer install --optimize-autoloader --no-interaction \
    && php artisan config:clear

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
