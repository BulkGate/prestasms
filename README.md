
<img src="https://portal.bulkgate.com/images/products/ps.svg" width="300" />®


http://www.presta-sms.com/

# Development
## PrestaShop
- [Documentation for developers](https://devdocs.prestashop-project.org/8/modules/creation/tutorial/)
- [Legacy/Core/Adapter/PrestaShopBundle](https://devdocs.prestashop-project.org/1.7/development/architecture/file-structure/understanding-src-folder/)
- [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
## Doctrine
- [Documentation for doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#executestatement)

Nektere sluzby v PrestaShopu maji pouze identifikator v ramci DI kontejneru (prestashop.adapter.data_provider.order_state, atd...). To znamena, ze pokud k nim nechceme pristupovat pres ServiceLocator,
musime jim vytvorit alias.
```yml
PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider: '@prestashop.adapter.data_provider.order_state'
```
Takto budu moct pouzit sluzbu pomoci type hintu :)

## Setup prestashop
Go to administration "Advanced parameters > Performance".
Cache - no
Disable all overrides - yes
Debug mode - yes (config/defines.inc.php:29 _PS_MODE_DEV = true)

## bin/console
CLI nastroj pro prestashop (instalace, odinstalace modulu, atd...)
```sh
sudo -u www-data bash # prihlasim se pod www-data usera
bin/console prestashop:module install bg_prestasms
# Toto by se mohlo vyresit rovnou ve sluzbe prestashop v docker-compose
```

## Proxy
Nektere informace je nutne ukladat na strane modulu (settings, login atp..). Proto jsou tyto akce implementovany na strane modulu:
- authenticate
- login
- logout
- settings


# External Links
- [platform stats](https://storeleads.app/reports/prestashop)
- [https://www.prestasoo.com/](https://www.prestasoo.com/)

# TODO
- Lifecycle modulu (instalace, odinstalace, aktivace, deaktivace) Legacy environment https://devdocs.prestashop-project.org/1.7/modules/concepts/services/#services-in-legacy-environment -> musime doladit Module class
  - usporadat service config podle legacy environmentu (abychom mohli prave v Modulu a na frontu pouzivat DI kontejner s nasema sluzbama)
  - pouzit tabs zpusob pro definovani menu
- Hooky
  - asset, cron, direct -> 
  - napojeni na kontejner
# bugs:
- UrlGeneratorInterface::ABSOLUTE_URL -> potrebujeme pri odhlaseni a prihlaseni, aby aplikace spravne presmerovavala. // https://github.com/PrestaShop/PrestaShop/issues/18703 - z nejakeho duvodu proste nefunguje ABSOLUTE_URL
