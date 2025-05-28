# Docker image
Tento image je určen pro účely vývoje prestaSMS modulu. Image můžete použít k napojení do IDE a také ke spuštění webové aplikace

## Konfigurace
Nastavit můžete `PRESTASHOP_VERSION` + `PHP_VERSION`.

```
.docker/.env
```

## Vytvoření image
Vytvoří image a současně do adresáře `prestashop` zkopíruje obsah aplikace z image.

```shell
.docker/generate.sh
```