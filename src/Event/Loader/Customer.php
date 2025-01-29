<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Helpers;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Strict;
use BulkGate\Plugin\Utils\Strings;

class Customer implements DataLoader
{
    use Strict;

    public function load(Variables $variables, array $parameters = []): void
    {
        $customer = null;

        if (isset($parameters['customer']) && $parameters['customer'] instanceof \Customer) {
            $customer = $parameters['customer'];
        } elseif (isset($variables['customer_id'])) {
            $customer = new \Customer($variables['customer_id']);
        }

		if (!$customer) {
			return;
		}

		$variables['customer_firstname'] = $customer->firstname;
		$variables['customer_lastname'] = $customer->lastname;
		$variables['customer_email'] = $customer->email;

		$address_id = (int) \Address::getFirstCustomerAddressId($customer->id);
		$id_address_delivery = $variables['id_address_delivery'] ?? null;
		$id_address_invoice = $variables['id_address_invoice'] ?? null;

		if ($address_id) {
			if ($id_address_delivery && $id_address_delivery !== $address_id) { // shipping address has precedence
				$this->address($variables, new \Address($id_address_delivery, $variables['lang_id']));
			} else {
				$this->address($variables, new \Address($address_id, $variables['lang_id']));
			}
		}

		if ($id_address_invoice) {
			$this->address($variables, new \Address($id_address_invoice, $variables['lang_id']), true);
		}
    }

    private function address(Variables $variables, \Address $address, $invoice = false): void
    {
        $prefix = $invoice ? 'customer_invoice' : 'customer';

        $variables["{$prefix}_firstname"] = $address->firstname;
        $variables["{$prefix}_lastname"] = $address->lastname;
        $variables["{$prefix}_country_id"] = Strings::lower(\Country::getIsoById($address->id_country));
        $variables["{$prefix}_company"] = $address->company;
        $variables["{$prefix}_phone"] = $address->phone;
        $variables["{$prefix}_mobile"] = $address->phone_mobile;
        $variables["{$prefix}_address"] = Helpers::joinStreet('address1', 'address2', ['address1' => $address->address1, 'address2' => $address->address2], []);
        $variables["{$prefix}_postcode"] = $address->postcode;
        $variables["{$prefix}_city"] = $address->city;
        $variables["{$prefix}_country"] = $address->country;
        $variables["{$prefix}_vat_number"] = $address->vat_number;
    }
}
