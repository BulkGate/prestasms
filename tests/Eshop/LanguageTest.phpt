<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Database\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Eshop\Language;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class LanguageTest extends TestCase
{
	public function testLoad(): void
	{
		$psLanguage = Mockery::mock('alias:Language');
		$psLanguage->shouldReceive('getLanguages')->once()->andReturn([
			['iso_code' => 'cs', 'name' => 'Čeština'],
			['iso_code' => 'en', 'name' => 'English'],
		]);

		$language = new Language();

		Assert::same([
			'cs' => 'Čeština',
			'en' => 'English',
		], $language->load());
	}

	public function testGet(): void
	{
		$psLanguage = Mockery::mock('alias:Language');
		$psLanguage->shouldReceive('getIsoById')->with(8)->andReturn('cs');
		$psLanguage->shouldReceive('getIsoById')->with(99)->andReturn(null);

		$language = new Language();
		Assert::same('cs', $language->get(8));
		Assert::same('en', $language->get(99));
		Assert::same('en', $language->get(null));
	}

	public function testHasMultiLanguageSupport(): void
	{
		$language = new Language();

		Assert::true($language->hasMultiLanguageSupport());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new LanguageTest())->run();
