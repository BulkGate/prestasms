echo "PrestaSMS module - preinstall"

cd /var/www/html/modules/bg_prestasms && \
rm composer.lock && \
composer install --prefer-dist --no-progress --no-dev && \
chown -R www-data:www-data vendor

exit 0