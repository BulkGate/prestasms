<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Strict;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class OrderStatus implements DataLoader
{
    use Strict;

    public function load(Variables $variables, array $parameters = []): void
    {
        if (isset($variables['order_status_id'])) {
            $status = new \OrderState((int) $variables['order_status_id'], (int) $variables['lang_id']);
            $variables['order_status'] = $status->name;
        }
    }
}
