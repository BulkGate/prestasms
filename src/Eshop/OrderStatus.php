<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop, Strict};
use PrestaShop\PrestaShop\Adapter\{Employee\ContextEmployeeProvider, OrderState\OrderStateDataProvider};

class OrderStatus implements Eshop\OrderStatus
{
	use Strict;

	private OrderStateDataProvider $order_state;

	private ContextEmployeeProvider $employee;

	public function __construct(OrderStateDataProvider $order_state, ContextEmployeeProvider $employee)
	{
		$this->order_state = $order_state;
		$this->employee = $employee;
	}


	public function load(): array
	{
		$output = [];
		$list = $this->order_state->getOrderStates($this->employee->getLanguageId());

		foreach ($list as ['id_order_state' => $state_id, 'name' => $name])
		{
			$output[$state_id] = $name;
		}

		return $output;
	}
}
