<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Loader\Customer};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class CustomerTest extends TestCase
{
	public function testLoad(): void
	{
		$customer = Mockery::mock('overload:Customer');
		$customer->shouldReceive('__construct')->with(123)->set('id', 123)->set('firstname', 'Jan')->set('lastname', 'Novák')->set('email', 'jan.novak@example.com');

		$address = Mockery::mock('overload:Address');
		$address->shouldReceive('getFirstCustomerAddressId')->with(123)->andReturn(10);
		$address->shouldReceive('__construct')->with(10, 8)->set('firstname', 'Jan')->set('lastname', 'Novák')->set('id_country', 56)->set('company', 'Firma')->set('phone', '123456789')->set('phone_mobile', '987654321')->set('address1', 'Ulice 1')->set('address2', 'Patro 2')->set('postcode', '11000')->set('city', 'Praha')->set('country', 'Česká republika')->set('vat_number', 'CZ12345678');

		$country = Mockery::mock('overload:Country');
		$country->shouldReceive('getIsoById')->with(56)->andReturn('CZ');

		$helpers = Mockery::mock('alias:BulkGate\Plugin\Event\Helpers');
		$helpers->shouldReceive('joinStreet')->with('address1', 'address2', ['address1' => 'Ulice 1', 'address2' => 'Patro 2'], [])->andReturn('Ulice 1, Patro 2');

		$variables = new Variables([
			'customer_id' => 123,
			'lang_id' => 8,
		]);

		$loader = new Customer();
		$loader->load($variables);

		Assert::same([
			'customer_id' => 123,
			'lang_id' => 8,
			'customer_firstname' => 'Jan',
			'customer_lastname' => 'Novák',
			'customer_email' => 'jan.novak@example.com',
			'customer_country_id' => 'cz',
			'customer_company' => 'Firma',
			'customer_phone' => '123456789',
			'customer_mobile' => '987654321',
			'customer_address' => 'Ulice 1, Patro 2',
			'customer_postcode' => '11000',
			'customer_city' => 'Praha',
			'customer_country' => 'Česká republika',
			'customer_vat_number' => 'CZ12345678',
		], $variables->toArray());
	}

	public function testNotCustomerId(): void
	{
		$loader = new Customer();
		$variables = new Variables();
		$loader->load($variables);
		Assert::same([], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new CustomerTest())->run();

