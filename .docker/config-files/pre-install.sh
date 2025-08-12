echo "PrestaSMS module - preinstall"

cd /var/www/html/modules/bulkgate_sms_module_for_prestashop && \
rm composer.lock && \
composer install --prefer-dist --no-progress --no-dev && \
chown -R www-data:www-data vendor

exit 0