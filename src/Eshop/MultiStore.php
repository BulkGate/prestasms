<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop, Strict};
use PrestaShop\PrestaShop\Core\Multistore\MultistoreContextCheckerInterface;

class MultiStore implements Eshop\MultiStore
{
	use Strict;

	private MultistoreContextCheckerInterface $multistore;

	public function __construct(MultistoreContextCheckerInterface $multistore)
	{
		$this->multistore = $multistore;
	}

	public function load(): array
	{
		$output = [];

		/**
		 * @phpstan-ignore-next-line
		 */
		foreach ($this->multistore->getShops() as ['id_shop' => $id, 'name' => $name])
		{
			$output[$id] = $name;
		}

		return $output;
	}
}
