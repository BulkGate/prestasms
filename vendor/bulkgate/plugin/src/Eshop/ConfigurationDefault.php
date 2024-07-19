<?php declare(strict_types=1);

namespace BulkGate\Plugin\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class ConfigurationDefault implements Configuration
{
	use Strict;

	private string $url;
	private string $product;

	private string $version;

	private string $name;

	public function __construct(string $url, string $product, string $version, string $name)
	{
		$this->url = $url;
		$this->product = $product;
		$this->version = $version;
		$this->name = $name;
	}


	public function url(): string
	{
		return $this->url;
	}


	public function product(): string
	{
		return $this->product;
	}


	public function version(): string
	{
		return $this->version;
	}


	public function name(): string
	{
		return $this->name;
	}
}
