<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\Strict;
use PrestaShop\PrestaShop\Core\Multistore\MultistoreContextCheckerInterface;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
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

        /*
         * @phpstan-ignore-next-line
         */
        foreach ($this->multistore->getShops() as ['id_shop' => $id, 'name' => $name]) {
            $output[$id] = $name;
        }

        return $output;
    }
}
