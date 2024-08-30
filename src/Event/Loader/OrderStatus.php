<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Strict;

class OrderStatus implements DataLoader
{
    use Strict;

    public function load(Variables $variables, array $parameters = []): void
    {
        if (isset($variables['order_status_id'])) {
            $status = new \OrderState($variables['order_status_id'], $variables['lang_id']);
            $variables['order_status'] = $status->name;
        }
    }
}
