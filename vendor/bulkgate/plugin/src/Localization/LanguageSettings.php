<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Settings\Settings};

class LanguageSettings implements Language
{
	use Strict;

	public const DefaultLanguage = 'en';

	private ?string $iso;

	private Settings $settings;

	public function __construct(?string $iso, Settings $settings)
	{
		$this->iso = $iso;
		$this->settings = $settings;
	}


	public function get(): string
	{
		$settings_iso = $this->settings->load('main:language');

		if (in_array($settings_iso, ['auto', null], true))
		{
			$settings_iso === null && $this->set('auto');

			return $this->iso ?? self::DefaultLanguage;
		}

		return $settings_iso;
	}


	public function set(?string $iso): void
	{
		$this->settings->set('main:language', $iso ?? 'auto', ['type' => 'string']);
	}
}
