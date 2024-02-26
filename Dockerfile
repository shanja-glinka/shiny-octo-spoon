FROM wyveo/nginx-php-fpm:php73

# pdate php
RUN apt-get update && apt-get install -y php7.3-http php7.3-raphf php7.3-propro && apt-get -y install mariadb-client && apt-get -y install redis-tools
	
ADD app /var/www/app

# instal node and run vue compiller	
RUN mkdir /var/nodeinstall \
    && cd /var/nodeinstall \
    && wget https://deb.nodesource.com/setup_16.x \
    && bash setup_16.x \
    && apt-get install -y nodejs

#nginx config
RUN rm /etc/nginx/conf.d/default.conf
ADD conf/nginx/default.conf /etc/nginx/conf.d/default.conf

#yii configs
#RUN rm /var/www/app/common/config/main-local.php
#ADD conf/yii/main-local.php /var/www/app/common/config/main-local.php

ADD startupScript.sh startupScript.sh
#RUN chmod 755 startupScript.sh
#ENTRYPOINT ["/startupScript.sh"]
