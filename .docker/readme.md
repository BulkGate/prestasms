# Docker image
Tento image je určen pro účely vývoje prestaSMS modulu. Image můžete použít k napojení do IDE a také ke spuštění webové aplikace.
Image obsahuje instalaci composeru a xdebugu. [Seznam dostupných images](https://github.com/BulkGate/prestasms/pkgs/container/prestasms).

## 1. Konfigurace
Nastavit můžete verzi php a prestashopu.

```
.docker/versions.json
```

## 2. Vytvoření image
Spusťte shell script.

```shell
.docker/generate.sh # vyrvori image a pushne do registru
.docker/generate.sh --no-push # vytvori image, nebude image pushovat do registru
```

> Každé spuštění scriptu vygeneruje 2 image a to (ghcr.io/bulkgate/prestasms) pro lokální použití a (ghcr.io/bulkgate/prestasms:8.2.0-8.1) pro nahrání do registru


## 3. Použití image


```yaml
prestashop:
    image: ghcr.io/bulkgate/prestasms:8.2.0-8.1
```