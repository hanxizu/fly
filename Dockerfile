# 使用官方 PHP 镜像作为基础镜像
FROM php:8.2-apache

# 启用 mod_rewrite
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制本地的应用代码到容器中
COPY . /var/www/html/

# 设置正确的权限
RUN chown -R www-data:www-data /var/www/html

# 暴露 80 端口
EXPOSE 80
