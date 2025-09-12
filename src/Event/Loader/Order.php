<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Loader;

use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Localization\Formatter;
use BulkGate\Plugin\Strict;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Order implements DataLoader
{
    use Strict;

    private Formatter $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function load(Variables $variables, array $parameters = []): void
    {
        if (!isset($variables['order_id'])) {
            return;
        }

        $order = isset($parameters['order']) && $parameters['order'] instanceof \Order ? $parameters['order'] : new \Order((int) $variables['order_id']);

        $currency = (array) \Currency::getCurrency($order->id_currency);

        $variables['lang_id'] ??= $order->id_lang;
        $variables['id_address_delivery'] = (int) $order->id_address_delivery;
        $variables['id_address_invoice'] = (int) $order->id_address_invoice;

        $variables['long_order_id'] = \sprintf('%06d', $variables['order_id']);
        $variables['cart_id'] = (int) $order->id_cart;
        $variables['carrier_id'] = (int) $order->id_carrier;
        $variables['order_payment'] = $order->payment;
        $variables['order_currency'] = $currency['iso_code'] ?? null;
        $variables['order_total_paid'] = $this->formatter->format('number', $order->total_paid);
        $variables['order_total_locale'] = $this->formatter->format('price', $order->total_paid, $variables['order_currency']);
        $variables['order_reference'] = $order->reference;

        $variables['order_datetime'] = $order->date_add ? $this->formatter->format('datetime', $order->date_add) : '-';
        $variables['order_date'] = $this->formatter->format('date', $order->date_add);
        $date = new \DateTime((string) $order->date_add);
        $variables['order_date1'] = $date->format('d.m.Y');
        $variables['order_date2'] = $date->format('d/m/Y');
        $variables['order_date3'] = $date->format('d-m-Y');
        $variables['order_date4'] = $date->format('Y-m-d');
        $variables['order_date5'] = $date->format('m.d.Y');
        $variables['order_date6'] = $date->format('m/d/Y');
        $variables['order_date7'] = $date->format('m-d-Y');
        $variables['order_time'] = $this->formatter->format('time', $order->date_add);
        $variables['order_time1'] = $date->format('H:i');

        if ($variables['carrier_id']) {
            $carrier = new \Carrier((int) $variables['carrier_id'], (int) $variables['lang_id']);
            $order_carrier = new \OrderCarrier((int) $variables['order_id'], (int) $variables['lang_id']);

            $variables['order_carrier_name'] = $carrier->name;
			/** @phpstan-ignore nullCoalesce.property */
            $variables['order_carrier_url'] = str_replace('@', $order_carrier->tracking_number ?? "", $carrier->url);
            $variables['order_carrier_delay'] = $carrier->delay;
            $variables['order_carrier_tracking_number'] = $order_carrier->tracking_number;
            $variables['order_carrier_tracking_date'] = $order_carrier->date_add ? $this->formatter->format('datetime', $order_carrier->date_add) : '-';
            $variables['order_carrier_price'] = $this->formatter->format('number', $order_carrier->shipping_cost_tax_incl);
            $variables['order_carrier_weight'] = $this->formatter->format('number', $order_carrier->weight);
            $variables['order_carrier_price_locale'] = $this->formatter->format('price', $order_carrier->shipping_cost_tax_incl, $variables['order_currency']);
        }

        $message = \Message::getMessagesByOrderId((int) $variables['order_id']);

        if (isset($message['message'])) {
            $variables['order_message'] = $message['message'];
        }

        $this->products($variables);

        if (isset($variables['return_id'])) {
            $this->returnProducts($variables, $order);
        }
    }

    private function products(Variables $variables): void
    {
        $p1 = $p2 = $p3 = $p4 = $pr1 = $pr2 = $pr3 = $pr4 = [];

        $list = \OrderDetail::getList((int) $variables['order_id']);

        $filter = $variables['filter_products'] ?? [];

        foreach ($list as $row) {
            if (empty($filter) || in_array((int) $row['id_order_detail'], (array) $filter)) {
                $p1[] = $row['product_quantity'] . 'x ' . $row['product_name'] . ' ' . $row['product_reference'];
                $p2[] = $row['product_quantity'] . 'x ' . $row['product_name'];
                $p3[] = $row['product_quantity'] . 'x (' . $row['product_id'] . ')' . $row['product_name'] . ' ' . $row['product_reference'];
                $p4[] = $row['product_quantity'] . 'x ' . $row['product_reference'];

                $price = $this->formatter->format('price', $row['product_price'], $variables['order_currency']);

                $pr1[] = $row['product_quantity'] . ',' . $row['product_name'] . ',' . $price;
                $pr2[] = $row['product_quantity'] . ';' . $row['product_name'] . ';' . $price;
                $pr3[] = $row['product_quantity'] . ',' . $row['product_reference'] . ',' . $price;
                $pr4[] = $row['product_quantity'] . ';' . $row['product_reference'] . ';' . $price;
            }
        }

        $variables['order_products1'] = implode('; ', $p1);
        $variables['order_products2'] = implode('; ', $p2);
        $variables['order_products3'] = implode('; ', $p3);
        $variables['order_products4'] = implode('; ', $p4);

        $variables['order_products5'] = implode("\n", $p1);
        $variables['order_products6'] = implode("\n", $p2);
        $variables['order_products7'] = implode("\n", $p3);
        $variables['order_products8'] = implode("\n", $p4);

        $variables['order_smsprinter1'] = implode(';', $pr1);
        $variables['order_smsprinter2'] = implode(';', $pr2);
        $variables['order_smsprinter3'] = implode(';', $pr3);
        $variables['order_smsprinter4'] = implode(';', $pr4);
    }

    private function returnProducts(Variables $variables, \Order $order): void
    {
        $return = new \OrderReturn((int) $variables['return_id'], (int) $variables['lang_id'], (int) $order->id_shop);
        $return_detail = \OrderReturn::getOrdersReturnProducts($return->id, $order);

        $p1 = $p2 = $p3 = $p4 = [];

        foreach ($return_detail as $row) {
            $p1[] = $row['product_quantity'] . 'x ' . $row['product_name'] . ' ' . $row['product_reference'];
            $p2[] = $row['product_quantity'] . 'x ' . $row['product_name'];
            $p3[] = $row['product_quantity'] . 'x (' . $row['product_id'] . ')' . $row['product_name'] . ' ' . $row['product_reference'];
            $p4[] = $row['product_quantity'] . 'x ' . $row['product_reference'];
        }

        $variables['return_question'] = $return->question;
        $variables['return_products1'] = implode('; ', $p1);
        $variables['return_products2'] = implode('; ', $p2);
        $variables['return_products3'] = implode('; ', $p3);
        $variables['return_products4'] = implode('; ', $p4);

        $variables['return_products5'] = implode("\n", $p1);
        $variables['return_products6'] = implode("\n", $p2);
        $variables['return_products7'] = implode("\n", $p3);
        $variables['return_products8'] = implode("\n", $p4);
    }
}
