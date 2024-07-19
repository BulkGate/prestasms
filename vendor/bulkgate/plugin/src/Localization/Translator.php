<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Translator
{
	/**
	 * @param mixed ...$parameters
	 */
	public function translate(string $message, ...$parameters): string;


	public function getIso(): string;


	public function setIso(string $iso): void;
}
