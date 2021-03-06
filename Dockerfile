# Pull base Image​
FROM ubuntu:18.04

# Change default shell to bash
SHELL ["/bin/bash","-c"]

# Prepare and Install the required package​
RUN ln -fs /usr/share/zoneinfo/Asia/Jakarta /etc/localtime
RUN apt-get update && apt-get install software-properties-common -y
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get update && apt-get install -y curl git unzip php7.2 apache2 libapache2-mod-php7.2 php7.2-{mysql,json,mbstring,curl,gd,bcmath,intl,soap,xml,xdebug,zip} locales \
 && rm -rf /var/lib/apt/lists/* \
 ​&& localedef -i en_US -c -f UTF-8 -A /usr/share/locale/locale.alias en_US.UTF-8​

# Update the PHP.ini file, enable <? ?> tags and quieten logging.​
RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /etc/php/7.2/apache2/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /etc/php/7.2/apache2/php.ini

# Manually set up the apache environment variables​
ENV APACHE_RUN_USER www-data​
ENV APACHE_RUN_GROUP www-data​
ENV APACHE_LOG_DIR /var/log/apache2​
ENV APACHE_LOCK_DIR /var/lock/apache2​
ENV APACHE_PID_FILE /var/run/apache2.pid​
ENV LANG en_US.utf8​

# Expose apache.​
EXPOSE 80

# Copy this repo into place.
COPY . /var/www/site

# Update the default apache site with the config we've created.​
ADD config/apache/simple-recipe.conf /etc/apache2/sites-available/simple-recipe.conf

# Disable default configuration
RUN a2dissite 000-default.conf

# Share default web root
VOLUME /var/www/site/public

# Enable new configuration and mod rewrite
RUN a2ensite simple-recipe.conf && a2enmod rewrite && a2enmod php7.2

WORKDIR /var/www/site/public

# By default start up apache in the foreground, override with /bin/bash for interative.​
CMD /usr/sbin/apache2ctl -D FOREGROUND
