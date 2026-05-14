# BulkGate PrestaShop SMS module

# Lokální vývoj a konfigurace
Pro úpravu parametrů prostředí (např. verze PrestaShopu, doména, port) vytvoř v kořenovém adresáři soubor *.env* podle vzoru *.env.template*. Hodnoty v tomto souboru se použijí při spuštění kontejnerů:
```shell
cp .env.template .env
docker compose up -d 
```

Pokud potřebuješ spustit více instancí s různými verzemi nebo parametry najednou, nastav proměnné prostředí přímo v příkazové řádce (inline):
```shell
MYSQL_DATABASE=prestashop_7 ADMINER_PORT=9070 PRESTASHOP_IMAGE_VERSION=1.7.8.0-7.4 PRESTASHOP_DOMAIN=ps7.dev.bulkgate.com:8070 PRESTASHOP_PORT=8070 docker compose --project-name ps_1_7 up -d
MYSQL_DATABASE=prestashop_8 ADMINER_PORT=9080 PRESTASHOP_IMAGE_VERSION=8.2.1-7.4 PRESTASHOP_DOMAIN=ps8.dev.bulkgate.com:8080 PRESTASHOP_PORT=8080 docker compose --project-name ps_8_2 up -d
MYSQL_DATABASE=prestashop_9 ADMINER_PORT=9090 PRESTASHOP_IMAGE_VERSION=9.0.0-1.0-classic-8.4 PRESTASHOP_DOMAIN=localhost:8090 PRESTASHOP_PORT=8090 docker compose --project-name ps_9_0 up -d
```

Aby ti fungovalo napovídání v IDE (prestashop source code), musíš namountovat instalaci prestashopu z kontejneru na disk.
```shell
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d 
```
Pokud používáš oba compose soubory, tak pred kazdou zmenou konfigurace bysi mel spustit:
```shell
rm -rf prestashop config*.xml
```

> Je to z toho duvodu, ze prestashop je v tuto chvili persistentne ulozen v adresari prestashop na tvojem disku a image by tak pri startupu neprovedl instalaci! 
> Soubory config.xml vznikaji prave pri instalaci modulu a je lepsi je take odstranit.

## PrestaShop
- [Documentation for developers](https://devdocs.prestashop-project.org/8/modules/creation/tutorial/)
- [Legacy/Core/Adapter/PrestaShopBundle](https://devdocs.prestashop-project.org/1.7/development/architecture/file-structure/understanding-src-folder/)
- [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
- [example modules](https://github.com/PrestaShop/example-modules)
- [how to](https://devdocs.prestashop-project.org/8/modules/sample-modules/order-pages-new-hooks/module-base/)
## Doctrine
- [Documentation for doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#executestatement)

