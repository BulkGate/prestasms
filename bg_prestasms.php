<?php

use BulkGate\PrestaSms, BulkGate\Extensions;
use BulkGate\Plugin\Settings\Settings;
use BulkGate\PrestaSms\Eshop\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Bg_PrestaSms extends Module
{
    public $tabs = [
        [
            'name' => 'BulkGate SMS',
            'class_name' => 'AdminPrestaSms',
            'parent_class_name' => 'CONFIGURE',
            'visible' => true,
            'icon' => 'desktop_windows'
        ]
    ];

    public function __construct()
    {
        $this->name = 'bg_prestasms';
        $this->tab = 'emailing';
        $this->version = '5.0.10';
        $this->author = 'TOPefekt s.r.o.';
        $this->author_uri = 'https://www.bulkgate.com/';

        parent::__construct();

        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        ];

        $this->displayName = 'PrestaSMS';
        $this->description = $this->l('Extend your PrestaShop store capabilities. Send personalized bulk SMS messages. Notify your customers about order status via customer SMS notifications. Receive order updates via Admin SMS notifications.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        PrestaSms\DI\Factory::setup(fn () => ['db' => $this->get('doctrine.dbal.default_connection')]);
    }


    public function getContent()
    {
        // we have dedicated controller.
        \Tools::redirectAdmin($this->context->link->getAdminLink('AdminPrestaSms'));
    }


    public function install()
    {
        $install = parent::install();

        PrestaSms\DI\Factory::get()->getByClass(Settings::class)->install();
        $this->installHooks();

        return $install;
    }


    public function uninstall()
    {
        $uninstall = parent::uninstall();

        PrestaSms\DI\Factory::get()->getByClass(Settings::class)->uninstall();

        return $uninstall;
    }


    public function installHooks()
    {
        $this->registerHook('actionOrderStatusPostUpdate');
        $this->registerHook('actionValidateOrder');
        $this->registerHook('actionCustomerAccountAdd');
        $this->registerHook('actionOrderReturn');
        $this->registerHook('actionOrderSlipAdd');
        $this->registerHook('actionAdminOrdersTrackingNumberUpdate');
        $this->registerHook('actionPaymentConfirmation');
        $this->registerHook('actionProductDelete');
        $this->registerHook('actionProductOutOfStock');
        $this->registerHook('actionProductCancel');
        $this->registerHook('actionEmailSendBefore');
        $this->registerHook('actionPrestaSmsSendSms');
        $this->registerHook('actionPrestaSmsExtendsVariables');
        $this->registerHook('displayAdminOrderSide');
    }


    public function hookActionOrderStatusPostUpdate(array $params)
    {
        if(isset($params['id_order']) && isset($params['newOrderStatus']))
        {
            $order = new Order((int) $params['id_order']);

            if($order->id !== null)
            {
                return $this->runHook('order_status_change_'.$params['newOrderStatus']->id, new Extensions\Hook\Variables(array(
                    'order_status_id' => $params['newOrderStatus']->id,
                    'order_id' => (int) $order->id,
                    'lang_id' => (int) $order->id_lang,
                    'store_id' => (int) $order->id_shop,
                    'customer_id' => (int) $order->id_customer
                )));
            }
        }
        return true;
    }


    public function hookActionValidateOrder(array $params)
    {
        if(isset($params['order']) && $params['order'] instanceof Order)
        {
            return $this->runHook('order_new', new Extensions\Hook\Variables(array(
                'order_id' => (int) $params['order']->id,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
                'customer_id' => (int) $params['order']->id_customer
            )));
        }
        return true;
    }


    public function hookActionCustomerAccountAdd(array $params)
    {
        if(isset($params['newCustomer']) && $params['newCustomer'] instanceof Customer)
        {
            return $this->runHook('customer_new', new Extensions\Hook\Variables(array(
                'customer_id' => (int) $params['newCustomer']->id,
                'lang_id' => (int) $params['newCustomer']->id_lang,
                'store_id' => (int) $params['newCustomer']->id_shop
            )));
        }
        return true;
    }


    public function hookActionOrderReturn(array $params)
    {
        if(isset($params['orderReturn']) && $params['orderReturn'] instanceof OrderReturn)
        {
            return $this->runHook('order_product_return', new Extensions\Hook\Variables(array(
                'return_id' => (int) $params['orderReturn']->id,
                'customer_id' => (int) $params['orderReturn']->id_customer,
                'order_id' => (int) $params['orderReturn']->id_order,
                'lang_id' => (int) $params['orderReturn']->id_lang,
                'store_id' => (int) $params['orderReturn']->id_shop
            )));
        }
        return true;
    }


    public function hookActionOrderSlipAdd(array $params)
    {
        if(isset($params['order']) && $params['order'] instanceof Order)
        {
            return $this->runHook('order_slip_add', new Extensions\Hook\Variables(array(
                'order_id' => (int) $params['order']->id,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
                'filter_products' => array_keys(isset($params['qtyList']) ? $params['qtyList'] : array())
            )));
        }
        return true;
    }


    public function hookActionAdminOrdersTrackingNumberUpdate(array $params)
    {
        if(isset($params['order']) && $params['order'] instanceof Order)
        {
            return $this->runHook('order_tracking_number', new Extensions\Hook\Variables(array(
                'order_id' => (int) $params['order']->id,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop
            )));
        }
        return true;
    }


    public function hookActionPaymentConfirmation(array $params)
    {
        if(isset($params['id_order']))
        {
            $order = new Order($params['id_order']);

            if($order->id !== null)
            {
                return $this->runHook('order_payment_confirmation', new Extensions\Hook\Variables(array(
                    'order_id' => (int) $order->id,
                    'lang_id' => (int) $order->id_lang,
                    'store_id' => (int) $order->id_shop,
                    'customer_id' => (int) $order->id_customer
                )));
            }
        }
        return true;
    }


    public function hookActionProductDelete(array $params)
    {
        if(isset($params['product']) && $params['product'] instanceof Product)
        {
            return $this->runHook('product_delete', new Extensions\Hook\Variables(array(
                'store_id' => (int) $params['product']->id_shop_default,
                'product_id' => (int) $params['product']->id,
            )));
        }
        return true;
    }


    public function hookActionUpdateQuantity(array $params)
    {
        if(isset($params['id_product']))
        {
            $product = new Product((int) $params['id_product']);

            return $this->runHook('product_update_quantity', new Extensions\Hook\Variables(array(
                'store_id' => (int) $product->id_shop_default,
                'product_id' => (int) $product->id,
                'id_product_attribute' => isset($params['id_product_attribute']) ? (int) $params['id_product_attribute'] : null,
            )));
        }
        return true;
    }


    public function hookActionProductOutOfStock(array $params)
    {
        if(isset($params['product']) && $params['product'] instanceof Product)
        {
            if((int) $params['product']->quantity <= (int) $params['product']->minimal_quantity)
            {
                if(Extensions\Helpers::outOfStockCheck($this->settings, (int) $params['product']->id))
                {
                    $this->runHook('product_out_of_stock', new Extensions\Hook\Variables(array(
                        'store_id' => (int) $params['product']->id_shop_default,
                        'product_id' => (int) $params['product']->id,
                    )));
                }
            }
        }
    }


    public function hookActionProductCancel(array $params)
    {
        if(isset($params['order']) && $params['order'] instanceof Order)
        {
            return $this->runHook('order_product_cancel', new Extensions\Hook\Variables(array(
                'order_id' => (int) $params['order']->id,
                'id_order_detail' => isset($params['id_order_detail']) ? $params['id_order_detail'] : null,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop
            )));
        }
        return true;
    }


    public function hookActionEmailSendBefore(array $params)
    {
        if(isset($params['templateVars']) && isset($params['template']) && $params['template'] === 'contact')
        {
            $customer_message = isset($params['templateVars']['{message}']) ? $params['templateVars']['{message}'] : null;

            if($customer_message !== null)
            {
                return $this->runHook('contact_form', new Extensions\Hook\Variables(array(
                    'customer_email' => isset($params['templateVars']['{email}']) ? $params['templateVars']['{email}'] : null,
                    'customer_message' => $customer_message,
                    'customer_message_short_50' => substr($customer_message, 0, 50),
                    'customer_message_short_80' => substr($customer_message, 0, 80),
                    'customer_message_short_100' => substr($customer_message, 0, 100),
                    'customer_message_short_120' => substr($customer_message, 0, 120),
                    'lang_id' => isset($params['idLang']) ? (int) $params['idLang'] : null,
                    'store_id' => isset($params['idShop']) ? (int) $params['idShop'] : null
                )));
            }
        }
        return true;
    }


    public function hookActionPrestaSmsSendSms(array $params)
    {
        $number = $params['number'] ?? null;
        $template = $params['template'] ?? null;
        $variables = $params['variables'] ?? [];
        $settings = $params['settings'] ?? [];

        $hook = $this->get('bulkgate.plugin.event.hook');

        $hook->send('/api/2.0/advanced/transactional', [
            'number' => $number,
            'application_product' => 'ps',
            'tag' => 'module_custom',
            'variables' => $variables,
            'country' => $settings['country'] ?? null,
            'channel' => [
                'sms' => [
                    'sender_id' => $settings['senderType'] ?? 'gSystem',
                    'sender_id_value' => $settings['senderValue'] ?? '',
                    'unicode' => $settings['unicode'] ?? false,
                    'text' => $template
                ]
            ]
        ]);
    }


    public function hookDisplayAdminOrderSide(array $params)
    {
        ['id_order' => $id] = $params;

        $settings = $this->get('bulkgate.plugin.settings.settings');
        $sign = $this->get('bulkgate.plugin.user.sign');
        $url = $this->get('bulkgate.plugin.io.url');

        $order = new Order($id); //todo: service factory
        $address = $order->getAddress();
        $country = $order->getCountry($address);

        $token = $sign->authenticate();

        if ($settings->load("static:application_token", false)) //todo: isModuleLoggedIn
        {
            return $this->render('@Modules/bg_prestasms/views/templates/send-message.html.twig', [
                'token' => $token,
                'url' => $url,
                'address' => $address,
                'country' => $country,
            ]);
        }

        return null;
    }


    public function hookActionPrestaSmsExtendsVariables(array $params)
    {
    }


    private function render(string $template, array $params = [])
    {
        $twig = $this->get('twig');

        return $twig->render($template, $params);
    }


    public function runHook($name, Extensions\Hook\Variables $variables)
    {
        $hook = new Extensions\Hook\Hook(
            $this->ps_di->getModule()->getUrl('/module/hook'),
            $variables->get('lang_id', (int) $this->context->language->id),
            $variables->get('store_id', (int) $this->context->shop->id),
            $this->ps_di->getConnection(),
            $this->settings,
            new PrestaSms\HookLoad($this->ps_di->getDatabase(), $this->context)
        );

        try
        {
            $hook->run((string) $name, $variables);
            return true;
        }
        catch (Extensions\IO\ConnectionException $e)
        {
            return false;
        }
    }
}
