<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Loader;

use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Localization\Formatter;
use BulkGate\Plugin\Strict;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Product implements DataLoader
{
    use Strict;

    private Formatter $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function load(Variables $variables, array $parameters = []): void
    {
        if (!isset($variables['product_id'])) {
            return;
        }

        $product = isset($parameters['product']) && $parameters['product'] instanceof \Product ? $parameters['product'] : new \Product((int) $variables['product_id'], false, null, (int) $variables['shop_id']);

        $variables['product_name'] = \Product::getProductName((int) $product->id);
        $variables['product_description'] = \strip_tags(\is_array($product->description_short) ? $product->description_short[$variables['lang_id']] : $product->description);
        $variables['product_manufacturer'] = $product->manufacturer_name;
        $variables['product_price'] = $this->formatter->format('number', $product->price);
        $variables['product_price_locale'] = $this->formatter->format('price', $product->price, $variables['shop_currency']);
        $variables['product_quantity'] = \Product::getQuantity((int) $product->id);
        $variables['product_minimal_quantity'] = (int) $product->minimal_quantity;
        $variables['product_ref'] = $product->reference;
        $variables['product_supplier'] = \Supplier::getNameById($product->id_supplier) ?: null;
        $variables['product_supplier_ref'] = $product->supplier_reference;
        $variables['product_supplier_id'] = $product->id_supplier;
        $variables['product_ean13'] = $product->ean13;
        $variables['product_upc'] = $product->upc;
        $variables['product_isbn'] = $product->isbn;
    }
}
