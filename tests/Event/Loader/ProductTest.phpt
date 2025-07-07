<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{PrestaShop\Event\Loader\Product, Plugin\Event\Variables, Plugin\Localization\Formatter};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ProductTest extends TestCase
{
	public function testLoad(): void
	{
		$product = Mockery::mock('overload:Product');
		$product->shouldReceive('__construct')->with(123, false, null, 451)->set('id', 123)->set('description_short', ['8' => 'Short desc'])->set('description', 'Long desc')->set('manufacturer_name', 'TestMan')->set('price', 99.99)->set('minimal_quantity', 2)->set('reference', 'REF123')->set('id_supplier', 5)->set('supplier_reference', 'SUPREF')->set('ean13', 'EAN123')->set('upc', 'UPC123')->set('isbn', 'ISBN123');
		$product->shouldReceive('getQuantity')->with(123)->andReturn(10);
		$product->shouldReceive('getProductName')->with(123)->andReturn('Test Product');

		$supplier = Mockery::mock('overload:Supplier');
		$supplier->shouldReceive('getNameById')->with(5)->once()->andReturn('SupplierName');

		$variables = new Variables([
			'product_id' => 123,
			'shop_id' => 451,
			'lang_id' => 8,
			'shop_currency' => 'CZK',
		]);

		$loader = new Product($formatter = Mockery::mock(Formatter::class));
		$formatter->shouldReceive('format')->with('number', 99.99)->once()->andReturn('99,99');
		$formatter->shouldReceive('format')->with('price', 99.99, 'CZK')->once()->andReturn('99,99 CZK');

		$loader->load($variables);

		Assert::same([
			'product_id' => 123,
			'shop_id' => 451,
			'lang_id' => 8,
			'shop_currency' => 'CZK',
			'product_name' => 'Test Product',
			'product_description' => 'Short desc',
			'product_manufacturer' => 'TestMan',
			'product_price' => '99,99',
			'product_price_locale' => '99,99 CZK',
			'product_quantity' => 10,
			'product_minimal_quantity' => 2,
			'product_ref' => 'REF123',
			'product_supplier' => 'SupplierName',
			'product_supplier_ref' => 'SUPREF',
			'product_supplier_id' => 5,
			'product_ean13' => 'EAN123',
			'product_upc' => 'UPC123',
			'product_isbn' => 'ISBN123',
		], $variables->toArray());
	}


	public function testNotProductId(): void
	{
		$loader = new Product(Mockery::mock(Formatter::class));
		$loader->load($variables = new Variables());

		Assert::same([], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ProductTest())->run();

