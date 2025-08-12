echo "BulkGate module - postinstall"

su -s /bin/bash www-data -c "bin/console prestashop:module install bulkgate_sms_module_for_prestashop"

exit 0