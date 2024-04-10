<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop, Strict};
use PrestaShop\PrestaShop\Adapter\OrderReturnState\OrderReturnStateDataProvider;
use PrestaShop\PrestaShop\Adapter\Employee\ContextEmployeeProvider;

class ReturnStatus implements Eshop\ReturnStatus
{
	use Strict;

    private OrderReturnStateDataProvider $order_state;

    private ContextEmployeeProvider $employee;

    public function __construct(OrderReturnStateDataProvider $order_state, ContextEmployeeProvider $employee)
    {
        $this->order_state = $order_state;
        $this->employee = $employee;
    }

    public function load(): array
    {
        $list = $this->order_state->getOrderReturnStates($this->employee->getLanguageId());
        $output = [];

        foreach ($list as ['id_order_return_state' => $state_id, 'name' => $name])
        {
            $output[$state_id] = $name;
        }

        return $output;
    }
}
