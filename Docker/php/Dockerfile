FROM wyveo/nginx-php-fpm:php82

RUN apt-key adv --fetch-keys 'https://packages.sury.org/php/apt.gpg' > /dev/null 2>&1

RUN apt-get update && apt-get install -y curl php8.2-http php8.2-raphf mariadb-client

ADD Docker/nginx/default.conf /etc/nginx/conf.d/default.conf

ADD Docker/startupScript.sh startupScript.sh