echo "BulkGate module - postinstall"

su -s /bin/bash www-data -c "bin/console prestashop:module install bg_prestasms"
