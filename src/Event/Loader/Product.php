<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Event\Variables, Localization\Formatter, Strict, Event\DataLoader};
use function strip_tags;

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
		if (!isset($variables['product_id']))
		{
			return;
		}

		$product = isset($parameters['product']) && $parameters['product'] instanceof \Product ? $parameters['product'] : new \Product((int) $variables['product_id']);

		$variables['product_name'] = $product->name;
		$variables['product_description'] = strip_tags(implode('', $product->description_short));
		$variables['product_manufacturer'] = $product->manufacturer_name;
		$variables['product_supplier'] = $product->supplier_name;
		$variables['product_price'] = $this->formatter->format("number", $product->price);
		$variables['product_price_locale'] = $this->formatter->format("price", $product->price); //todo: currency
		$variables['product_quantity'] = (int) $product->quantity;
		$variables['product_minimal_quantity'] = (int) $product->minimal_quantity;
		$variables['product_ref'] = $product->reference;
		$variables['product_supplier_ref'] = $product->supplier_reference;
		$variables['product_ean13'] = $product->ean13;
		$variables['product_upc'] = $product->upc;
		$variables['product_supplier_id'] = $product->id_supplier;
		$variables['product_isbn'] = $product->isbn;
	}
}
