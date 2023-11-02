<?php
namespace BulkGate\PrestaSms;

use BulkGate;
use BulkGate\Extensions\Hook\Variables;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookLoad extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Hook\ILoad
{
    /** @var Database */
    private $db;

    /** @var \Context */
    private $context;

    /** @var BulkGate\Extensions\ILocale */
    private $locale;

    public function __construct(Database $db, \Context $context)
    {
        $this->db = $db;
        $this->context = $context;
        $this->locale = new BulkGate\Extensions\LocaleSimple();
    }

    public function language(Variables $variables)
    {
        if($variables->get('lang_id'))
        {
            $lang = new \Language($variables->get('lang_id'), $variables->get('lang_id'));

            if(extension_loaded('intl'))
            {
                $this->locale = new BulkGate\Extensions\LocaleIntl($lang->locale);
            }
            else
            {
                $this->locale = new BulkGate\Extensions\LocaleSimple($lang->date_format_lite);
            }
            $variables->set('language_iso', \Language::getIsoById((int) $variables->get('lang_id')));
        }
    }

    public function order(Variables $variables)
    {
        if($variables->get('order_id'))
        {
            $order = new \Order($variables->get('order_id'), $variables->get('lang_id', null));
            $currency = \Currency::getCurrency($order->id_currency);

            $variables->set('long_order_id', sprintf("%06d", $variables->get('order_id')));

            $variables->set('customer_id', $order->id_customer);
            $date = new \DateTime($order->date_add);

            $variables->set('id_address_delivery', (int) $order->id_address_delivery);
            $variables->set('id_address_invoice', (int) $order->id_address_invoice);
            $variables->set('order_payment', $order->payment);
            $variables->set('order_currency', isset($currency['iso_code']) ? $currency['iso_code'] : null);
            $variables->set('order_currency_symbol', isset($currency['sign']) ? $currency['sign'] : null);
            $variables->set('order_total_paid', number_format($order->total_paid, 2));
            $variables->set('order_total_locale', $this->locale->price($order->total_paid, $variables->get('order_currency')));
            $variables->set('order_total_paid_integer', (int) $order->total_paid);
            $variables->set('cart_id', (int) $order->id_cart);
            $variables->set('carrier_id', (int) $order->id_carrier);
            $variables->set('order_reference', $order->reference);

            $variables->set('order_datetime', $this->locale->datetime($date));
            $variables->set('order_date', $this->locale->date($date));
            $variables->set('order_date1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3.\\2.\\1', $order->date_add));
            $variables->set('order_date2', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3/\\2/\\1', $order->date_add));
            $variables->set('order_date3', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3-\\2-\\1', $order->date_add));
            $variables->set('order_date4', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\1-\\2-\\3', $order->date_add));
            $variables->set('order_date5', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2.\\3.\\1', $order->date_add));
            $variables->set('order_date6', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2/\\3/\\1', $order->date_add));
            $variables->set('order_date7', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2-\\3-\\1', $order->date_add));
            $variables->set('order_time',  preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5',     $order->date_add));
            $variables->set('order_time1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5:\\6', $order->date_add));

            if($variables->get('carrier_id'))
            {
                $carrier = new \Carrier($variables->get('carrier_id'), $variables->get('lang_id', null));

                $variables->set('order_carrier_name', $carrier->name);
                $variables->set('order_carrier_delay', $carrier->delay);

                $order_carrier = $this->db->execute($this->db->prepare("SELECT `id_order_carrier` FROM `{$this->db->table('order_carrier')}` WHERE `id_order` = %s AND `id_carrier` = %s", array($variables->get('order_id'), $variables->get('carrier_id'))));

                if($order_carrier->getNumRows() > 0)
                {
                    $orderCarrier = new \OrderCarrier($order_carrier->getRow()->id_order_carrier, $variables->get('lang_id', null));
                    $variables->set('order_carrier_tracking_number', $orderCarrier->tracking_number);
                    $variables->set('order_carrier_tracking_date', $this->locale->datetime(new \DateTime($orderCarrier->date_add)));
                    $variables->set('order_carrier_price', number_format($orderCarrier->shipping_cost_tax_incl, 2));
                    $variables->set('order_carrier_weight', number_format($orderCarrier->weight, 2));
                    $variables->set('order_carrier_price_locale', $this->locale->price($orderCarrier->shipping_cost_tax_incl, $variables->get('order_currency')));
                    $variables->set('order_carrier_url', str_replace('@', $orderCarrier->tracking_number, $carrier->url));
                }
            }

            $message = \Message::getMessageByCartId($variables->get('cart_id'));

            if(is_array($message) && isset($message['message']))
            {
                $variables->set('order_message', $message['message']);
            }

            if($variables->get('id_address_delivery'))
            {
                $this->address($variables, (int) $variables->get('id_address_delivery'));
            }

            if($variables->get('id_address_invoice'))
            {
                $this->address($variables, (int) $variables->get('id_address_invoice'), 'invoice_');
            }

            $this->orderProducts($variables);
            $this->returnProducts($variables, $order);
        }
    }


    public function returnProducts(Variables $variables, \Order $order)
    {
        if($variables->get('return_id'))
        {
            $return = new \OrderReturn($variables->get('return_id'), $variables->get('lang_id'));

            $variables->set('return_question', $return->question);

            $return_detail = \OrderReturn::getOrdersReturnProducts($variables->get('return_id'), $order);

            $p1 = $p2 = $p3 = $p4 = array();

            foreach($return_detail as $row)
            {
                $p1[] = $row['product_quantity'].'x '.$row['product_name'].' '.$row['product_reference'];
                $p2[] = $row['product_quantity'].'x '.$row['product_name'];
                $p3[] = $row['product_quantity'].'x ('.$row['product_id'].')'.$row['product_name'].' '.$row['product_reference'];
                $p4[] = $row['product_quantity'].'x '.$row['product_reference'];
            }

            $variables->set("return_products1", implode("; ", $p1));
            $variables->set("return_products2", implode("; ", $p2));
            $variables->set("return_products3", implode("; ", $p3));
            $variables->set("return_products4", implode("; ", $p4));

            $variables->set("return_products5", implode("\n", $p1));
            $variables->set("return_products6", implode("\n", $p2));
            $variables->set("return_products7", implode("\n", $p3));
            $variables->set("return_products8", implode("\n", $p4));
        }
    }


    public function orderProducts(Variables $variables)
    {
        if($variables->get('order_id'))
        {
            $p1 = $p2 = $p3 = $p4 = $pr1 = $pr2 = $pr3 = $pr4 = array();

            $list = \OrderDetail::getList($variables->get('order_id'));

            $filter = $variables->get('filter_products', array());

            foreach($list as $row)
            {
                if(empty($filter) || in_array($row['id_order_detail'], $filter))
                {
                    $p1[] = $row['product_quantity'].'x '.$row['product_name'].' '.$row['product_reference'];
                    $p2[] = $row['product_quantity'].'x '.$row['product_name'];
                    $p3[] = $row['product_quantity'].'x ('.$row['product_id'].')'.$row['product_name'].' '.$row['product_reference'];
                    $p4[] = $row['product_quantity'].'x '.$row['product_reference'];

                    $pr1[] = $row['product_quantity'].','.$row['product_name'].','.$this->locale->price($row['product_price'], $variables->get('order_currency'));
                    $pr2[] = $row['product_quantity'].';'.$row['product_name'].';'.$this->locale->price($row['product_price'], $variables->get('order_currency'));
                    $pr3[] = $row['product_quantity'].','.$row['product_reference'].','.$this->locale->price($row['product_price'], $variables->get('order_currency'));
                    $pr4[] = $row['product_quantity'].';'.$row['product_reference'].';'.$this->locale->price($row['product_price'], $variables->get('order_currency'));
                }
            }

            $variables->set('order_products1', implode('; ', $p1));
            $variables->set('order_products2', implode('; ', $p2));
            $variables->set('order_products3', implode('; ', $p3));
            $variables->set('order_products4', implode('; ', $p4));


            $variables->set('order_products5', implode("\n", $p1));
            $variables->set('order_products6', implode("\n", $p2));
            $variables->set('order_products7', implode("\n", $p3));
            $variables->set('order_products8', implode("\n", $p4));

            $variables->set('order_smsprinter1', implode(';', $pr1));
            $variables->set('order_smsprinter2', implode(';', $pr2));
            $variables->set('order_smsprinter3', implode(';', $pr3));
            $variables->set('order_smsprinter4', implode(';', $pr4));

            $variables->set('filter_products', '-');
        }
    }


    public function customer(Variables $variables)
    {
        if($variables->get('customer_id'))
        {
            $customer = new \Customer($variables->get('customer_id'));

            $variables->set('customer_email', $customer->email, '', false);
            $variables->set('customer_lastname', $customer->lastname, '', false);
            $variables->set('customer_firstname', $customer->firstname, '', false);

            $address_id = (int) \Address::getFirstCustomerAddressId($variables->get('customer_id'));

            if($address_id !== $variables->get('id_address_delivery', -1) && $address_id !== $variables->get('id_address_invoice', -1))
            {
                $this->address($variables, $address_id);
            }
        }
    }


    public function address(Variables $variables, $address_id, $prefix = '')
    {
        $address = new \Address($address_id, $variables->get('lang_id'));

        $variables->set('customer_'.$prefix.'firstname', $address->firstname, '', false);
        $variables->set('customer_'.$prefix.'lastname', $address->lastname, '', false);

        $variables->set('customer_'.$prefix.'country_id', strtolower(\Country::getIsoById($address->id_country)), '', false);

        $variables->set('customer_'.$prefix.'company', $address->company, '', false);

        $variables->set('customer_'.$prefix.'phone', $address->phone, '', false);
        $variables->set('customer_'.$prefix.'mobile', $address->phone_mobile, '', false);

        if(strlen(trim($address->address1)) > 0)
        {
            $variables->set('customer_'.$prefix.'address', $address->address1 . ', ' . $address->address2, '', false);
        }
        else
        {
            $variables->set('customer_'.$prefix.'address', $address->address1, '', false);
        }

        $variables->set('customer_'.$prefix.'postcode', $address->postcode, '', false);
        $variables->set('customer_'.$prefix.'city', $address->city, '', false);

        $variables->set('customer_'.$prefix.'country', $address->country, '', false);

        ((int) $address->id_state !== 0) && $variables->set('customer_'.$prefix.'state', \State::getNameById((int) $address->id_state), '', false);

        $variables->set('customer_'.$prefix.'vat_number', $address->vat_number, '', false);
    }

    public function orderStatus(Variables $variables)
    {
        if($variables->get('order_status_id'))
        {
            $state = new \OrderState($variables->get('order_status_id'), $variables->get('lang_id', null));

            $variables->set('order_status', $state->name, $variables->get('order_status_id'));
        }
    }

    public function shop(Variables $variables)
    {
        $configuration = \Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_SHOP_PHONE'), $variables->get('lang_id', null));

        $variables->set('shop_email', isset($configuration['PS_SHOP_EMAIL']) ? $configuration['PS_SHOP_EMAIL'] : '');
        $variables->set('shop_phone', isset($configuration['PS_SHOP_PHONE']) ? $configuration['PS_SHOP_PHONE'] : '');

        $shop = new \Shop($variables->get('store_id'), $variables->get('lang_id', null));

        $variables->set('shop_name', $shop->name);
        $variables->set('shop_domain', $shop->domain);
    }

    public function product(Variables $variables)
    {
        if($variables->get('product_id'))
        {
            $product = new \Product($variables->get('product_id'), true, $variables->get('lang_id', null));

            $variables->set('product_name', $product->name);
            $variables->set('product_description', strip_tags($product->description_short));
            $variables->set('product_manufacturer', $product->manufacturer_name);
            $variables->set('product_supplier', $product->supplier_name);

            $variables->set('product_price', number_format($product->price, 2));
            $variables->set('product_price_locale', $this->locale->price($product->price, $this->context->currency->iso_code));

            $variables->set('product_quantity', (int) $product->quantity);
            $variables->set('product_minimal_quantity', (int) $product->minimal_quantity);

            $variables->set('product_ref', $product->reference);
            $variables->set('product_supplier_ref', $product->supplier_reference);
            $variables->set('product_ean13', $product->ean13);
            $variables->set('product_upc', $product->upc);
            $variables->set('product_supplier_id', $product->id_supplier);
            $variables->set('product_isbn', $product->isbn);
        }
    }

    public function employee(Variables $variables)
    {
        if($this->context->employee && $this->context->employee->id)
        {
            $employee = new \Employee($this->context->employee->id, $variables->get('lang_id', null));

            $variables->set('employee_id', $employee->id);
            $variables->set('employee_email', $employee->email);
            $variables->set('employee_firstname', $employee->firstname);
            $variables->set('employee_lastname', $employee->lastname);
        }
    }

    public function extension(Variables $variables)
    {
        if(class_exists('BulkGate\PrestaSMS\HookExtension'))
        {
            $hook = new HookExtension();
            $hook->extend($this->db, $variables);
        }
    }

    public function load(Variables $variables)
    {
        $this->language($variables);
        $this->order($variables);
        $this->orderStatus($variables);
        $this->customer($variables);
        $this->product($variables);
        $this->shop($variables);
        $this->employee($variables);
        $this->extension($variables);
    }
}
