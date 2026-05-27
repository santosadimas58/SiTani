FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev libonig-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip git curl gnupg ca-certificates sudo nginx supervisor \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN rm -rf node_modules vendor
RUN composer install --optimize-autoloader --no-interaction --no-dev
RUN npm install && npm run build

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN printf 'server {\n\
    listen 8080;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
    }\n\
}\n' > /etc/nginx/sites-available/default \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

RUN printf '[unix_http_server]\nfile=/var/run/supervisor.sock\nchmod=0700\n\n[supervisord]\nnodaemon=true\nlogfile=/dev/null\nlogfile_maxbytes=0\npidfile=/var/run/supervisord.pid\n\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory=supervisor.rpcinterface:make_main_rpcinterface\n\n[supervisorctl]\nserverurl=unix:///var/run/supervisor.sock\n\n[program:php-fpm]\ncommand=php-fpm -F\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n' > /etc/supervisord.conf

RUN printf '#!/bin/sh\n\
if [ "$#" -gt 0 ]; then\n\
    exec "$@"\n\
fi\n\
chmod -R 777 /var/www/html/storage\n\
chown -R www-data:www-data /var/www/html/storage\n\
php /var/www/html/artisan migrate --force\n\
php /var/www/html/artisan db:seed --force 2>/dev/null || true\n\
php /var/www/html/artisan storage:link 2>/dev/null || true\n\
php /var/www/html/artisan key:generate --force\n\
php /var/www/html/artisan config:clear\n\
php /var/www/html/artisan route:clear\n\
php /var/www/html/artisan cache:clear\n\
php /var/www/html/artisan view:clear\n\
exec /usr/bin/supervisord -c /etc/supervisord.conf\n\
' > /usr/local/bin/startup && chmod +x /usr/local/bin/startup

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/startup"]
