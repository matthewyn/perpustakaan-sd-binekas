FROM php:8.2-cli

# Install system dependencies for PHP & Node.js
RUN apt-get update && apt-get install -y \
    zip unzip wget curl libicu-dev gnupg2 \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# Install Node.js & npm (LTS version)
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && node -v \
    && npm -v

WORKDIR /usr/src/myapp

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install

# Install Node.js dependencies
COPY package.json package-lock.json ./
RUN npm install

# Copy the rest of the application
COPY . .

EXPOSE 8080
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8080"]
