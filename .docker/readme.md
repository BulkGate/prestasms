# Docker image
Tento image je určen pro účely vývoje BulkGate PrestaShop SMS modulu. Image můžete použít k napojení do IDE a také ke spuštění webové aplikace.
Image obsahuje instalaci composeru a xdebugu. [Seznam dostupných images](https://github.com/BulkGate/prestasms/pkgs/container/prestasms).

## 1. Definování matice verzí
V souboru versions.json jsou uloženy meta informace o matici dostupných verzí php a prestashop aplikace. Pokud tento seznam potřebujete rozšířit, tak stačí spustit:

```shell
.docker/generate.sh --php-version 8.4 --prestashop-version 9.0.0 --no-push
```

## 2. Vytvoření image
Pokud chcete pro lokální účely vytvořit image, který nechcete distribuovat do registru a ani přidat do versions.json, můžete spustit:


```shell
.docker/generate.sh --php-version=8.4 --prestashop-version=9.0.0 --no-push --no-emit-version
```

> Každé spuštění scriptu vygeneruje 2 image a to (ghcr.io/bulkgate/prestasms) pro lokální použití a (ghcr.io/bulkgate/prestasms:9.0.0-8.4) pro nahrání do registru


## 3. Použití image

```yaml
prestashop:
    image: ghcr.io/bulkgate/prestasms:8.2.1-8.1
```