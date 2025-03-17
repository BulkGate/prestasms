<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use OrderState;
use BulkGate\Plugin\{Event\DataLoader, Event\Variables, Strict};

class OrderStatus implements DataLoader
{
	use Strict;

	public function load(Variables $variables, array $parameters = []): void
	{
		if (isset($variables['order_status_id']))
		{
			$status = new OrderState($variables['order_status_id'], $variables['lang_id']);
			$variables['order_status'] = $status->name;
		}
	}
}
