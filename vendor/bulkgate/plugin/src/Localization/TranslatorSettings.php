<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Settings\Settings};
use function is_array, is_string;

class TranslatorSettings implements Translator
{
	use Strict;

	private Settings $settings;

	private Language $language;

	private ?string $iso = null;

	/**
	 * @var array<string, string>
	 */
	private array $translates;

	public function __construct(Settings $settings, Language $language)
	{
		$this->settings = $settings;
		$this->language = $language;
	}


	public function translate(string $message, ...$parameters): string
	{
		$this->init();

		return $this->translates[$message] ?? $message;
	}


	public function getIso(): string
	{
		return $this->iso ?? $this->language->get();
	}


	public function setIso(string $iso): void
	{
		$this->language->set($iso);

		$this->init($iso);
	}


	private function init(?string $iso = null): void
	{
		if (($iso !== null && $this->getIso() !== $iso) || !isset($this->translates))
		{
			$iso ??= $this->language->get();

			$this->iso = $iso;

			$translates = $this->settings->load("translates:$this->iso");

			$this->translates = is_array($translates) ? $translates : [];
		}
	}
}
