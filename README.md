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

## Known bugs

### Auto instalace modulu
Instalace modulu probehne v poradku (podle vypisu z console behem startupu kontejneru), ale modul nefunguje.
```text
You have requested a non-existent service "PrestaShop\PrestaShop\Adapter\Shop\Url\BaseUrlProvider"
```
Toto se vyresi jednoduse tak, ze v kontejneru spustis:
```shell
/tmp/post-install-scripts/post-install.sh
```

## PrestaShop
- [Documentation for developers](https://devdocs.prestashop-project.org/8/modules/creation/tutorial/)
- [Legacy/Core/Adapter/PrestaShopBundle](https://devdocs.prestashop-project.org/1.7/development/architecture/file-structure/understanding-src-folder/)
- [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
- [example modules](https://github.com/PrestaShop/example-modules)
- [how to](https://devdocs.prestashop-project.org/8/modules/sample-modules/order-pages-new-hooks/module-base/)
## Doctrine
- [Documentation for doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#executestatement)

Nektere sluzby v PrestaShopu maji pouze identifikator v ramci DI kontejneru (prestashop.adapter.data_provider.order_state, atd...). To znamena, ze pokud k nim nechceme pristupovat pres ServiceLocator,
musime jim vytvorit alias.

```yml
PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider: '@prestashop.adapter.data_provider.order_state'
```
>Takto budu moct pouzit sluzbu pomoci type hintu :)

## Common errors
Expected to find class "XXX" in file "xxx" while importing services from resource "../src/", but it was not found! Check the namespace prefix used with the resource. -> nejspis jsme zapomneli spustit: composer dumpautoload

## bin/console
CLI nastroj pro prestashop (instalace, odinstalace modulu, atd...)
```sh
sudo -u www-data bash # prihlasim se pod www-data usera
bin/console prestashop:module install bg_prestasms
# Toto by se mohlo vyresit rovnou ve sluzbe prestashop v docker-compose
```


# External Links
- [platform stats](https://storeleads.app/reports/prestashop)
- [https://www.prestasoo.com/](https://www.prestasoo.com/)

# TODO automatizace vs (admin/customer sms)
Aktualne se za pojem **automatizace** berou admin/customer sms. V blizke budoucnosti se ale automatizace zobecni a bude potreba lepe strukturovat data a tim padem prijit s lepsim API nez s Variables.
Ty bouzel funguji pouze na principu sberneho obejktu, ale uplne jim chybi kontext a treba taky pokrocilejsi metody (flatten -> podpora stavajiciho formatu).

Jak si dokazu predstavit "nove" Variables?

## Context index
V principu je to docela podobny koncept graphQL. Kontext tvori zakladni informace (strukturu) o tom, co se bude nacitat.
Kontext je vstup pro loader, ktery na zaklade klicu a hodnot kontextu bude moci naplnit struktury kontextu.

- id_shop -> id obchodu pro ktery kontext plati
- id_lang -> id jazyku pro ktery kontext plati
- id_currency -> id meny pro kterou kontext plati
- id_carrier -> id dopravce pro ktereho kontext plati
- id_order -> id objednavky pro ktery kontext plati
- id_product -> id produktu pro ktery kontext plati
- id_cart -> id kosiku pro ktery kontext plati
- id_user -> id uzivatele pro ktery kontext plati


### Příklad definice kontextu (index)
Takto může vypadat struktura kontextu

```json5
{
  id_shop: 1,
  id_lang: 1,
  order: {
    id_order: 5,
    id_lang: 1,
    id_product: [20, 30],
    id_carrier: 3,
  },
}
```

### Příklad načteného kontextu (resolved)
Takto může vypadat struktura resolvnutého indexu kontextu.

```json5
{
  shop: { //id_shop: 1
    id: 1,
    name: "Alza.cz",
    email: "support@alza.cz",
    currency: "CZK",
    phone: "800-600-500",
    url: "https://www.alza.cz"
  },
  lang: { //id_lang: 1
    id: 1,
    iso: "cs"
  },
  order: { //literal key
    id: 3,
    currency: "CZK",
    date: "2025-01-23T06:11:00.000Z",
    price: 169.9, //value in order currency
    lang: { //id_lang: 1
      id: 1,
      iso: "cs"
    },
    product: [ //id_product: [20, 30]
      {
        id: 20,
        quantity: 2,
        price: 11.9, //value in order currency
        name: "Mug Today is a good day",
        reference: "demo_13"
      },
      {
        id: 30,
        quantity: 1,
        price: 19.12, //value in order currency
        name: "Hummingbird printed t-shirt (Size: S - Color: Black)",
        reference: "demo_1"
      }
    ],
    carrier: { //id_carrier: 3
      price: 0, //relative to order
      name: "DPD",
      url: "https://dpd.com/tracinking/AXJFGKLOURT",
      weight: 300, //grams
      tracking_number: "AXJFGKLOURT",
      tracking_date: "2025-01-23T12:45:00.000Z"
    }
  }
}
```
### Vyhody nevyhody
- Samotne rozdeleni do kontextu ma pozitivni vliv na omezeni vyberu promennych. Uzivatel muze pracovat pouze s promennymi, ktere nabizi kontext, nebo ktere se daji na zaklade kontextu derivovat (foreach ,filtry, formatovace)
- Data budou vzdy strukturovana, takze budeme posilat mensi pocet dat (bez formatovacu a filtru) a budeme schopni vytvorit odpovidajici datove typy s truktury, ktere mohou byt zdokumentovany

### Automatizace todo
- kazda automatizace bude mit svuj vlastni soubor (neco jako github actions) 
- budu mit prehled o vsech spustenych instancich s tim, ze se budu moci podivat i na automatizacni graf (tzn. presne budu videt kam se interpret dostal a jake mel k dispozici data)
- napriklad z detailu (objednavky, kosiku, uzivatele atd..) budu moct otestovat automatizaci -> simulovat. Tim si vyzkousim nastaveni na realnych datech...
- kazda eshop platforma muze (a asi bude) mit vlastni automatizacni soubor, protoze moznosti kazde platformy jsou ruzne.
- mely by se automatizace verzovat (source)? Melo by verzovani byt na zaklade features/deprecations na dane platforme?
- pokud uzivatel vytvori automatizaci a nasledne ji bude spravovat, budou se tyto zmeny verzovat (stejne jako GTM)?
- pokud si uzivatel bude chtit otestovat automatizaci bude to spoustet dummy a nebo realne triggery?
- budou instance automatizaci bezet v dockeru?
  - prestashop-1.7 -> /app/model/automation/prestashop/1.7/ -> docker compose run prestashop-1.7
  - prestashop-8 -> /app/model/automation/prestashop/8/ -> docker compose run prestashop-8.0 -> volume bude vest ke zdrojovym souborum automatizace (runtime, triggery)
- docker image:
  - bude postaveny tak, aby se dal jednoduse zkonfigurovat (db connection, rabbit connection, network etc...)
  - vice mene to bude konzolove php, protoze neni duvod vyuzivat serverove
  - automatizace by se spoustely cronem ze strany bulkgate. Je otazka jestli je poustet primo. Abychom zajistili bezpecnost a asynchronni chovani 

Příklad toho, jak by se mohla volat automatizace

/api/1.0/eshop/automation/run/
```json5
{
  context: CONTEXT_INDEX,
  data: CONTEXT_INDEX_RESOLVED
}
```

# TODO
- docker image: vytvorit image, ktery bude resit (PHP_VERSION, PRESTASHOP_VERSION). Verze modulu je dana gitem. v pripade wordpressu to bude WORDPRESS_VERSION + WOOCOMMERCE_VERSION
- pri spusteni modulu (login) se neprenesou data modulu (pokud nejsou). Az po refreshi se nactou. To je lehce bug, protoze data muzou byt jina kvuli napr. synchronizaci...
- novy hook actionCustomerAccountUpdate -> muzeme aktualizovat informace o uzivateli
- preklady https://devdocs.prestashop-project.org/8/modules/creation/module-translation/new-system/
- zvalidovat modul oproti implementacim nativnich modulu napr. https://github.com/PrestaShop/example-modules/tree/master/demovieworderhooks
- dodelat nove metody - isLoggedIn atp ... budou to jenom ciste obalky nad standardnim $settings->load('static:token')
- proverit asynchronous order = -1 -> toto se stane, kdyz nastane chyba behem zpracovani. Musime zajistit, ze se tasky opet odeslou
- ~~zkusit marketing opt-in v checkoutu~~
- ~~dodelat cron~~
- ~~dopsat testy~~
- ~~sloty ve web komponente - detail objednavky SEND message button ?~~ Jeste je potreba zjistit, jak by se jednoduse daly navesit event handlery v ramci reactu pro slotted element resp. pokud navesim na <slot onClick>, tak chci, aby se event propagoval...
- ~~zprovoznit vsechny hooky~~
- ~~overit proxyRedirect v pripade zmeny jazyku v settings 192.168.32.3~~
- ~~zbavit se custom AsynchronousDatabase~~
- ~~implementovat rozhrani databaze~~
- ~~defaultni hodnoty pro nastaveni~~
- ~~browser asset cron nacitat i v admin casti~~
- ~~Lifecycle modulu (instalace, odinstalace, aktivace, deaktivace) Legacy environment https://devdocs.prestashop-project.org/1.7/modules/concepts/services/#services-in-legacy-environment -> musime doladit Module class~~
  - ~~usporadat service config podle legacy environmentu (abychom mohli prave v Modulu a na frontu pouzivat DI kontejner s nasema sluzbama)~~
  - ~~pouzit tabs zpusob pro definovani menu~~
- Hooky
  - ~~asset, cron, direct -> https://devdocs.prestashop-project.org/8/modules/creation/displaying-content-in-front-office/~~
  - ~~napojeni na kontejner~~
# bugs:
- ~~UrlGeneratorInterface::ABSOLUTE_URL -> potrebujeme pri odhlaseni a prihlaseni, aby aplikace spravne presmerovavala. // https://github.com/PrestaShop/PrestaShop/issues/18703 - z nejakeho duvodu proste nefunguje ABSOLUTE_URL~~ vyresilo se tak, ze se pouze meni url SPA.
- ~~pri prihlaseni modulu se nekdy zobrazuje last sync jako 01.01.1970~~
- ~~[Prestashop] pokud je aktivovany (default) Advanced parameters > Security > Back office token protection, tak router negeneruje ABSOLUTE_URL. To potom spatne funguje v pripadech, kdy potrebujeme hard reload (viz. zmena jazyka)~~
- [module:plugin] kdyz neni nastaveno synchronize="all", tak se potom nereflektuji automatizace. Na frontendu sice jdou videt (data z portalu), ale v modulu se realne nespusti! Je to z duvodu jak je implementovana metoda BulkGate\Plugin\Event\Dispatcher::check!!
Je to optimalizace kvuli rychlosti. Takhle ovsem o tom, co se odeslalo rozhodl modul, protoze vedel co ma nastaveno. Nyni to vsak vedet nebude, tim padem o tom bude muset rozhodnout BG. Mohlo by to do jiste miry mit i vyhodu v tom, ze si uzivatel toto bude moct nastavit na BG.
