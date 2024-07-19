<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Url
{
	use Strict;

	private string $url;

	public function __construct(string $url = 'https://portal.bulkgate.com')
	{
		$this->url = $url;
	}


	public function get(string $path = ''): string
	{
		return "$this->url/$path";
	}
}
