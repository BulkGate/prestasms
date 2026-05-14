<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop;

use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\Strict;
use PrestaShop\PrestaShop\Core\Employee\ContextEmployeeProviderInterface;
use PrestaShop\PrestaShop\Core\Order\OrderReturnStateDataProviderInterface;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class ReturnStatus implements Eshop\ReturnStatus
{
    use Strict;

    private OrderReturnStateDataProviderInterface $order_state;

    private ContextEmployeeProviderInterface $employee;

    public function __construct(OrderReturnStateDataProviderInterface $order_state, ContextEmployeeProviderInterface $employee)
    {
        $this->order_state = $order_state;
        $this->employee = $employee;
    }

    public function load(): array
    {
        $output = [];
        $list = $this->order_state->getOrderReturnStates($this->employee->getLanguageId());

        foreach ($list as ['id_order_return_state' => $state_id, 'name' => $name]) {
            $output[$state_id] = $name;
        }

        return $output;
    }
}
