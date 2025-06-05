<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop, Strict};
use Language as PrestaShopLanguage;
use function is_string;

class Language implements Eshop\Language
{
	use Strict;

	public function load(): array
	{
		$output = [];

		foreach (PrestaShopLanguage::getLanguages() as ['iso_code' => $iso, 'name' => $name])
		{
			$output[$iso] = $name;
		}

		return $output;
	}


	public function get(?int $id = null): string
	{
		if ($id === null)
		{
			return 'en';
		}

		$iso = PrestaShopLanguage::getIsoById($id);

		if (is_string($iso))
		{
			return $iso;
		}

		return 'en';
	}


	public function hasMultiLanguageSupport(): bool
	{
		return true;
	}
}
