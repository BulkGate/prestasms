
<img src="https://portal.bulkgate.com/images/products/ps.svg" width="300" />®


http://www.presta-sms.com/

# Development
[Documentation for developers](https://devdocs.prestashop-project.org/8/modules/creation/tutorial/)

## Setup prestashop
Go to administration "Advanced parameters > Performance".
Cache - no
Disable all overrides - yes
Debug mode - yes (config/defines.inc.php:29 _PS_MODE_DEV = true)

## bin/console
CLI nastroj pro prestashop (instalace, odinstalace modulu, atd...)
```sh
sudo -u www-data bash # prihlasim se pod www-data usera
# Toto by se mohlo vyresit rovnou ve sluzbe prestashop v docker-compose
```
# External Links
- [platform stats](https://storeleads.app/reports/prestashop)
- [https://www.prestasoo.com/](https://www.prestasoo.com/)

# TODO
- [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
