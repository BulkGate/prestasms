<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use function htmlspecialchars, str_replace, preg_match, json_encode, strpos, strpbrk;
use const JSON_INVALID_UTF8_SUBSTITUTE, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE;

/*
 * Taken over from Latte (https://latte.nette.org)
 */

class EscapeNette implements Escape
{
	use Plugin\Strict;

	public static function html(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
	}


	public static function js(string $s): string
	{
		return str_replace(
			[']]>', '<!', '</'],
			[']]\u003E', '\u003C!', '<\/'],
			json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: ''
		);
	}


	public static function url(string $s): string
	{
		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|(?:mailto|tel|sms):.+|[/?#].*|[^:]+)$~Di', $s) ? $s : '';
	}


	public static function htmlAttr(string $s, bool $double = true): string
	{
		if (strpos($s, '`') !== false && strpbrk($s, ' <>"\'') === false)
		{
			$s .= ' '; // protection against innerHTML mXSS vulnerability nette/nette#1496
		}

		$s = htmlspecialchars($s, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8', $double);

		return str_replace('{', '&#123;', $s);
	}
}
