FROM php:8.2-apache

# ビルド引数として UID と GID を定義
ARG USER_ID=1000
ARG GROUP_ID=1000

# Node.jsのセットアップ（Node.js 20を使用）
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

# PHPの拡張機能をインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache modリライトを有効化
RUN a2enmod rewrite

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Apacheのドキュメントルートを変更
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# npmのグローバルディレクトリを設定
ENV NPM_CONFIG_PREFIX=/var/www/.npm-global
ENV PATH=$PATH:/var/www/.npm-global/bin

# 必要なディレクトリの作成と権限設定
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/.npm-global \
    && mkdir -p /var/www/.npm \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www

# ユーザー設定
RUN usermod -u ${USER_ID} www-data && groupmod -g ${GROUP_ID} www-data

RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo "Asia/Tokyo" > /etc/timezone
