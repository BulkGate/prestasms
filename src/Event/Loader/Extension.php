<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Loader;

use BulkGate\Plugin\Database\Connection;
use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Strict;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Extension implements DataLoader
{
    use Strict;

    private Connection $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    public function load(Variables $variables, array $parameters = []): void
    {
        \Hook::exec('actionPrestaSmsExtendsVariables', [
            'variables' => $variables,
            'database' => $this->database,
        ], null, false, true, false, (int) $variables['shop_id']);
    }
}
