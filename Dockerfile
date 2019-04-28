FROM ubuntu:18.04
MAINTAINER unual <chengqiwenmail@gmail.com>
ENV REFRESHED_AT 2019-03-12

# based on dgraziotin/lamp
# MAINTAINER Daniel Graziotin <daniel@ineed.coffee>

ENV DOCKER_USER_ID 501
ENV DOCKER_USER_GID 20

ENV BOOT2DOCKER_ID 1000
ENV BOOT2DOCKER_GID 50

ENV PHPMYADMIN_VERSION=4.8.5

# Tweaks to give Apache/PHP write permissions to the app
RUN usermod -u ${BOOT2DOCKER_ID} www-data && \
    usermod -G staff www-data && \
    useradd -r mysql && \
    usermod -G staff mysql

RUN groupmod -g $(($BOOT2DOCKER_GID + 10000)) $(getent group $BOOT2DOCKER_GID | cut -d: -f1)
RUN groupmod -g ${BOOT2DOCKER_GID} staff

# Install packages
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get -y update && apt-get install -y \
git \
apache2 \
php7.2 \
libapache2-mod-php7.2 \
php7.2-bcmath \
php7.2-gd \
php7.2-json \
php7.2-sqlite \
php7.2-mysql \
php7.2-curl \
php7.2-xml \
php7.2-mbstring \
php7.2-zip \
mcrypt \
nano

RUN apt-get install locales
RUN locale-gen fr_FR.UTF-8
RUN locale-gen en_US.UTF-8
RUN locale-gen de_DE.UTF-8
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Add composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

# Apache conf
# config to enable .htaccess
ADD apache_default /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure /app folder with sample app
RUN mkdir -p /app && rm -fr /var/www/html && ln -s /app /var/www/html
RUN git clone https://github.com/unual/fas.git app
RUN echo "APP_NAME=Laravel \n APP_ENV=local \n APP_KEY=base64:2EFFwLK71Z9hNrU5soStzZw3hYr6dsq3jAiH9LX6D+4= \n APP_DEBUG=true \n APP_URL=http://localhost \n LOG_CHANNEL=stack \n DB_CONNECTION=mysql \n DB_HOST=106.14.116.192 \n DB_PORT=3306 \n DB_DATABASE=fas \n DB_USERNAME=fas \n DB_PASSWORD=mypassword \n BROADCAST_DRIVER=log \n CACHE_DRIVER=file \n QUEUE_CONNECTION=sync \n SESSION_DRIVER=file \n SESSION_LIFETIME=120 \n REDIS_HOST=127.0.0.1 \n REDIS_PASSWORD=null \n REDIS_PORT=6379 \n MAIL_DRIVER=smtp \n MAIL_HOST=smtp.mailtrap.io \n MAIL_PORT=2525 \n MAIL_USERNAME=null \n MAIL_PASSWORD=null \n MAIL_ENCRYPTION=null \n PUSHER_APP_ID= \n PUSHER_APP_KEY= \n PUSHER_APP_SECRET= \n PUSHER_APP_CLUSTER=mt1 \n MIX_PUSHER_APP_KEY=\"${PUSHER_APP_KEY}\" \n MIX_PUSHER_APP_CLUSTER=\"${PUSHER_APP_CLUSTER}\"" >> app/.env
#ADD app/ /app

EXPOSE 80
# start Apache2 on image start
CMD ["/usr/sbin/apache2ctl","-DFOREGROUND"]