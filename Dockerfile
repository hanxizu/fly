# 使用官方 PHP 镜像，基于 Apache
FROM php:8.2-apache

# 启用 mod_rewrite 以便处理 URL 重写（如果需要）
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制本地的应用代码到容器中的 Apache 根目录
COPY . /var/www/html/

# 设置 Apache 服务器的配置文件，允许 .htaccess 文件进行 URL 重写
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# 设置正确的权限
RUN chown -R www-data:www-data /var/www/html

# 暴露 80 端口
EXPOSE 80
