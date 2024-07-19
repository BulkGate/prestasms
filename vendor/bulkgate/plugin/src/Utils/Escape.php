<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Escape
{
	public static function html(string $s): string;


	public static function js(string $s): string;


	public static function url(string $s): string;


	public static function htmlAttr(string $s, bool $double = true): string;
}
