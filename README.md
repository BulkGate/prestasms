
<img src="https://portal.bulkgate.com/images/products/ps.svg" width="300" />®


http://www.presta-sms.com/

# Development
## PrestaShop
- [Documentation for developers](https://devdocs.prestashop-project.org/8/modules/creation/tutorial/)
- [Legacy/Core/Adapter/PrestaShopBundle](https://devdocs.prestashop-project.org/1.7/development/architecture/file-structure/understanding-src-folder/)
- [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
- [example modules](https://github.com/PrestaShop/example-modules)
- [how to](https://devdocs.prestashop-project.org/8/modules/sample-modules/order-pages-new-hooks/module-base/)
- [docker image](https://hub.docker.com/r/prestashop/prestashop)
## Doctrine
- [Documentation for doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#executestatement)

Nektere sluzby v PrestaShopu maji pouze identifikator v ramci DI kontejneru (prestashop.adapter.data_provider.order_state, atd...). To znamena, ze pokud k nim nechceme pristupovat pres ServiceLocator,
musime jim vytvorit alias.
```yml
PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider: '@prestashop.adapter.data_provider.order_state'
```
Takto budu moct pouzit sluzbu pomoci type hintu :)

```bash
cd /tmp
cp -r /var/www/html/modules/bg_prestasms/ bg_prestasms # copy project
zip -r prestasms-5.0.10.zip bg_prestasms -x "bg_prestasms/.git/*" -x "bg_prestasms/.idea/*" # create zip
cp prestasms-5.0.10.zip /var/www/html/modules/bg_prestasms/ # expose to project dir
```

## Common errors
Expected to find class "XXX" in file "xxx" while importing services from resource "../src/", but it was not found! Check the namespace prefix used with the resource. -> nejspis jsme zapomneli spustit: composer dumpautoload

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
- ~~defaultni hodnoty pro nastaveni~~
- ~~browser asset cron nacitat i v admin casti~~
- zvalidovat modul oproti implementacim nativnich modulu napr. https://github.com/PrestaShop/example-modules/tree/master/demovieworderhooks
- ~~Lifecycle modulu (instalace, odinstalace, aktivace, deaktivace) Legacy environment https://devdocs.prestashop-project.org/1.7/modules/concepts/services/#services-in-legacy-environment -> musime doladit Module class~~
  - ~~usporadat service config podle legacy environmentu (abychom mohli prave v Modulu a na frontu pouzivat DI kontejner s nasema sluzbama)~~
  - ~~pouzit tabs zpusob pro definovani menu~~
- Hooky
  - ~~asset, cron, direct -> https://devdocs.prestashop-project.org/8/modules/creation/displaying-content-in-front-office/~~
  - ~~napojeni na kontejner~~
# bugs:
- UrlGeneratorInterface::ABSOLUTE_URL -> potrebujeme pri odhlaseni a prihlaseni, aby aplikace spravne presmerovavala. // https://github.com/PrestaShop/PrestaShop/issues/18703 - z nejakeho duvodu proste nefunguje ABSOLUTE_URL
