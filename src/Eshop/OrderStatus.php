<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop;

use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\Strict;
use PrestaShop\PrestaShop\Adapter\Employee\ContextEmployeeProvider;
use PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
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

        foreach ($list as ['id_order_state' => $state_id, 'name' => $name]) {
            $output[$state_id] = $name;
        }

        return $output;
    }
}
