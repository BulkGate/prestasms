<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop, Strict};

class Order
{
	use Strict;

    private \Order $order;

    public function __construct(int $id)
    {
        $this->order = new \Order($id);
    }

    public function getAddress($prefers_billing = false): \Address
    {
        $shipping_address = new \Address($this->order->id_address_delivery);
        $billing_address = new \Address($this->order->id_address_invoice);

        if ($prefers_billing)
        {
            if (empty($billing_address->phone_mobile ?? $billing_address->phone ?? null))
            {
                return $shipping_address;
            }

            return $billing_address;
        }
        else
        {
            if (empty($shipping_address->phone_mobile ?? $shipping_address->phone ?? null))
            {
                return $billing_address;
            }

            return $shipping_address;
        }
    }

    public function getCountry(\Address $address): \Country
    {
        return new \Country($address->id_country);
    }
}
