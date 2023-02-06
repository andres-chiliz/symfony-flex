ARG PHP_IMAGE=475320849898.dkr.ecr.eu-west-3.amazonaws.com/php
ARG NGINX_IMAGE=475320849898.dkr.ecr.eu-west-3.amazonaws.com/nginx
ARG BASE_IMAGE_VERSION=v4.1.0

FROM ${PHP_IMAGE}:${BASE_IMAGE_VERSION}-development AS php-development
ENV APP_ENV=test
RUN git config --global --add safe.directory /shared
WORKDIR /shared
