FROM nginx:1.9.14

################################################################################
# Build instructions
################################################################################

# 更新时区
RUN echo "Asia/Shanghai" > /etc/timezone
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai  /etc/localtime

# 替换成阿里云的源，加速更新
RUN sed -i -e "s/deb.debian.org/mirrors.aliyun.com/g" /etc/apt/sources.list
RUN sed -i -e "s/security.debian.org/mirrors.aliyun.com\/debian-security/g" /etc/apt/sources.list
RUN sed -i -e "s/^.*httpredir.debian.org.*$//g" /etc/apt/sources.list
RUN echo 'deb http://mirrors.aliyun.com/debian jessie main' >> /etc/apt/sources.list

# Remove default nginx configs.
RUN rm -f /etc/nginx/conf.d/*

# Install packages
RUN apt-get update && apt-get install -my supervisor curl wget php5-curl php5-fpm php5-gd php5-memcached php5-mysql php5-mcrypt php5-sqlite php5-xdebug php-apc

# Ensure that PHP5 FPM is run as root.
RUN sed -i "s/user = www-data/user = root/" /etc/php5/fpm/pool.d/www.conf
RUN sed -i "s/group = www-data/group = root/" /etc/php5/fpm/pool.d/www.conf

# Pass all docker environment
RUN sed -i '/^;clear_env = no/s/^;//' /etc/php5/fpm/pool.d/www.conf

# Get access to FPM-ping page /ping
RUN sed -i '/^;ping\.path/s/^;//' /etc/php5/fpm/pool.d/www.conf
# Get access to FPM_Status page /status
RUN sed -i '/^;pm\.status_path/s/^;//' /etc/php5/fpm/pool.d/www.conf

# Prevent PHP Warning: 'xdebug' already loaded.
# XDebug loaded with the core
RUN sed -i '/.*xdebug.so$/s/^/;/' /etc/php5/mods-available/xdebug.ini

# Add configuration files
COPY conf/nginx.conf /etc/nginx/
COPY conf/supervisord.conf /etc/supervisor/conf.d/
COPY conf/php.ini /etc/php5/fpm/conf.d/40-custom.ini
COPY conf/php.ini /etc/php5/cli/conf.d/40-custom.ini

# Add php extension files
COPY lib/mongodb.so /usr/lib/php5/20131226/mongodb.so
COPY lib/redis.so /usr/lib/php5/20131226/redis.so
RUN echo 'extension = mongodb.so' > /etc/php5/mods-available/mongodb.ini && ln -s /etc/php5/mods-available/mongodb.ini /etc/php5/fpm/conf.d/ && ln -s /etc/php5/mods-available/mongodb.ini /etc/php5/cli/conf.d/
RUN echo 'extension = redis.so' > /etc/php5/mods-available/redis.ini && ln -s /etc/php5/mods-available/redis.ini /etc/php5/fpm/conf.d/ && ln -s /etc/php5/mods-available/redis.ini /etc/php5/cli/conf.d/


################################################################################
# Volumes
################################################################################

VOLUME ["/var/www", "/etc/nginx/conf.d"]

################################################################################
# Ports
################################################################################

EXPOSE 80 8282 8283 8284

################################################################################
# Entrypoint
################################################################################

ENTRYPOINT ["/usr/bin/supervisord"]
