<?php

use BulkGate\Extensions;
use BulkGate\Plugin\Event\Dispatcher;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Settings\Settings;
use BulkGate\PrestaSms;
use BulkGate\PrestaSms\Eshop\Order as PrestaSmsOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Bg_PrestaSms extends Module
{
    public $tabs = [
        [
            'name' => 'BulkGate SMS',
            'class_name' => 'AdminPrestaSmsConfigure',
            'parent_class_name' => 'CONFIGURE',
            'visible' => true,
            'icon' => 'send_to_mobile',
        ],
        [
            'name' => 'Debug',
            'class_name' => 'AdminPrestaSmsDebug',
            'parent_class_name' => 'AdminPrestaSmsConfigure',
            'visible' => true,
            'icon' => 'debug',
        ],
    ];

    public function __construct()
    {
        $this->name = 'bg_prestasms';
        $this->tab = 'emailing';
        $this->version = '5.0.10';
        $this->author = 'BulkGate';
        $this->author_uri = 'https://www.bulkgate.com/';

        parent::__construct();

        $this->ps_versions_compliancy = [
            'min' => '7.7.0',
            'max' => '8.99.99',
        ];

        $this->displayName = 'PrestaSMS';
        $this->description = $this->l('Extend your PrestaShop store capabilities. Send personalized bulk SMS messages. Notify your customers about order status via customer SMS notifications. Receive order updates via Admin SMS notifications.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        PrestaSms\DI\Factory::setup(fn () => ['db' => $this->get('doctrine.dbal.default_connection')]);
    }

    public function getContent()
    {
        // we have dedicated controller.
        Tools::redirectAdmin($this->get('router')->generate('bulkgate_main_app', []));
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
        // $this->registerHook('actionProductOutOfStock');
        $this->registerHook('actionProductCancel');
        $this->registerHook('actionEmailSendBefore');
        $this->registerHook('actionPrestaSmsSendSms');
        $this->registerHook('actionPrestaSmsExtendsVariables');
        $this->registerHook('displayAdminOrderSide');

        $this->registerHook('displayHeader');
        $this->registerHook('displayBackOfficeHeader');
    }

    // DONE
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        if (isset($params['id_order']) && isset($params['newOrderStatus'])) {
            $order = new Order((int) $params['id_order']);

            if ($order->id !== null) {
                $this->runHook('order', 'change-status', new Variables([
                    'order_status_id' => $params['newOrderStatus']->id,
                    'order_id' => (int) $order->id,
                    'lang_id' => (int) $order->id_lang,
                    'store_id' => (int) $order->id_shop,
                    'customer_id' => (int) $order->id_customer,
                ]), ['order' => $order]);
            }
        }
    }

    // DONE
    public function hookActionValidateOrder(array $params)
    {
        if (isset($params['order']) && $params['order'] instanceof Order) {
            $this->runHook('order', 'new', new Variables([
                'order_id' => (int) $params['order']->id,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
                'customer_id' => (int) $params['order']->id_customer,
            ]), ['order' => $params['order']]);
        }
    }

    // DONE
    public function hookActionCustomerAccountAdd(array $params)
    {
        if (isset($params['newCustomer']) && $params['newCustomer'] instanceof Customer) {
            $this->runHook('customer', 'new', new Variables([
                'customer_id' => (int) $params['newCustomer']->id,
                'lang_id' => (int) $params['newCustomer']->id_lang,
                'store_id' => (int) $params['newCustomer']->id_shop,
            ]), ['customer' => $params['newCustomer']]);
        }
    }

    // DONE
    public function hookActionOrderReturn(array $params)
    {
        if (isset($params['orderReturn']) && $params['orderReturn'] instanceof OrderReturn) {
            $this->runHook('return', 'new', new Variables([
                'return_id' => (int) $params['orderReturn']->id,
                'customer_id' => (int) $params['orderReturn']->id_customer,
                'order_id' => (int) $params['orderReturn']->id_order,
                'lang_id' => (int) $params['orderReturn']->getAssociatedLanguage()->id,
                'store_id' => (int) $params['orderReturn']->getShopId(),
            ]));
        }
    }

    // DONE
    public function hookActionOrderSlipAdd(array $params)
    {
        if (isset($params['order']) && $params['order'] instanceof Order) {
            $this->runHook('order', 'TODO_slip_add', new Variables([
                'order_id' => (int) $params['order']->id,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
                'filter_products' => array_keys(isset($params['qtyList']) ? $params['qtyList'] : []),
            ]), ['order' => $params['order']]);
        }
    }

    // DONE - tracking number je prazdne
    public function hookActionAdminOrdersTrackingNumberUpdate(array $params)
    {
        if (isset($params['order']) && $params['order'] instanceof Order) {
            $this->runHook('order', 'tracking-number', new Variables([
                'order_id' => (int) $params['order']->id,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
            ]), ['order' => $params['order'], 'carrier' => $params['carrier']]);
        }
    }

    // DONE
    public function hookActionPaymentConfirmation(array $params)
    {
        if (isset($params['id_order'])) {
            $order = new Order($params['id_order']);

            if ($order->id !== null) {
                $this->runHook('order', 'payment', new Variables([
                    'order_id' => (int) $order->id,
                    'lang_id' => (int) $order->id_lang,
                    'store_id' => (int) $order->id_shop,
                    'customer_id' => (int) $order->id_customer,
                ]), ['order' => $order]);
            }
        }
    }

    // DONE
    public function hookActionProductDelete(array $params)
    {
        if (isset($params['product']) && $params['product'] instanceof Product) {
            $this->runHook('product', 'TODO_delete', new Variables([
                'store_id' => (int) $params['product']->id_shop_default,
                'product_id' => (int) $params['product']->id,
            ]), ['product' => $params['product']]);
        }
    }

    public function hookActionUpdateQuantity(array $params)
    {
        if (isset($params['id_product'])) {
            $product = new Product((int) $params['id_product']);

            $this->runHook('product', 'TODO_update_quantity', new Variables([
                'store_id' => (int) $product->id_shop_default,
                'product_id' => (int) $product->id,
                'id_product_attribute' => isset($params['id_product_attribute']) ? (int) $params['id_product_attribute'] : null,
            ]), ['product' => $product]);
        }
    }

    /*public function hookActionProductOutOfStock(array $params)
    {
        if(isset($params['product']) && $params['product'] instanceof Product)
        {
            if((int) $params['product']->quantity <= (int) $params['product']->minimal_quantity)
            {
                if(Extensions\Helpers::outOfStockCheck($this->settings, (int) $params['product']->id))
                {
                    $this->runHook('product', 'out-of-stock', new Variables([
                        'store_id' => (int) $params['product']->id_shop_default,
                        'product_id' => (int) $params['product']->id,
                    ]), ['product' => $params['product']]);
                }
            }
        }
    }*/

    // DONE
    public function hookActionProductCancel(array $params)
    {
        if (isset($params['order']) && $params['order'] instanceof Order) {
            $this->runHook('order', 'TODO_product_cancel', new Variables([
                'order_id' => (int) $params['order']->id,
                'id_order_detail' => $params['id_order_detail'] ?? null,
                'customer_id' => (int) $params['order']->id_customer,
                'lang_id' => (int) $params['order']->id_lang,
                'store_id' => (int) $params['order']->id_shop,
            ]), ['order' => $params['order']]);
        }
    }

    // todo: kde se to pouziva???
    public function hookActionEmailSendBefore(array $params)
    {
        if (isset($params['templateVars']) && isset($params['template']) && $params['template'] === 'contact') {
            $customer_message = isset($params['templateVars']['{message}']) ? $params['templateVars']['{message}'] : null;

            if ($customer_message !== null) {
                $this->runHook('contact', 'form', new Variables([
                    'customer_email' => isset($params['templateVars']['{email}']) ? $params['templateVars']['{email}'] : null,
                    'customer_message' => $customer_message,
                    'customer_message_short_50' => substr($customer_message, 0, 50),
                    'customer_message_short_80' => substr($customer_message, 0, 80),
                    'customer_message_short_100' => substr($customer_message, 0, 100),
                    'customer_message_short_120' => substr($customer_message, 0, 120),
                    'lang_id' => isset($params['idLang']) ? (int) $params['idLang'] : null,
                    'store_id' => isset($params['idShop']) ? (int) $params['idShop'] : null,
                ]));
            }
        }
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
                    'text' => $template,
                ],
            ],
        ]);
    }

    public function hookDisplayAdminOrderSide(array $params)
    {
        ['id_order' => $id] = $params;

        $settings = $this->get('bulkgate.plugin.settings.settings');
        $sign = $this->get('bulkgate.plugin.user.sign');
        $url = $this->get('bulkgate.plugin.io.url');

        $order = new PrestaSmsOrder($id); // todo: service factory
        $address = $order->getAddress();
        $country = $order->getCountry($address);

        $token = $sign->authenticate();

        if ($settings->load('static:application_token', false)) { // todo: isModuleLoggedIn
            return $this->render($this->getModuleTemplatePath() . 'send-message.html.twig', [
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

    public function hookDisplayHeader()
    {
        return $this->asynchronousAsset();
    }

    public function hookDisplayBackOfficeHeader()
    {
        // $this->test();
        return $this->asynchronousAsset();
    }

    /*private function test()
    {
        $order = new Order(5);
        $this->runHook('order', 'TEST-order', new Variables([
            // 'order_status_id' => 3,
            // 'product_id' => 19,
            'order_id' => (int) $order->id,
            'lang_id' => (int) $order->id_lang,
            'store_id' => (int) $order->id_shop,
            'customer_id' => (int) $order->id_customer,
        ]), ['order' => $order]);
    }*/

    /**
     * Render a twig template.
     */
    private function render(string $template, array $params = []): string
    {
        /** @var Twig_Environment $twig */
        $twig = $this->get('twig');

        return $twig->render($template, $params);
    }

    /**
     * Get path to this module's template directory
     */
    private function getModuleTemplatePath(): string
    {
        return sprintf('@Modules/%s/views/templates/', $this->name);
    }

    private function runHook(string $category, string $endpoint, Variables $variables, array $parameters = [], ?callable $success_callback = null): void
    {
        /** @var Dispatcher */
        $dispatcher = $this->get('bulkgate.plugin.event.dispatcher');

        $dispatcher->dispatch($category, $endpoint, $variables, $parameters, $success_callback);
    }

    private function asynchronousAsset()
    {
        $settings = $this->get('bulkgate.plugin.settings.settings');

        if ($settings->load('main:dispatcher') === Dispatcher::Asset) {
            return '<script type="text/javascript" src="' . $this->context->link->getModuleLink($this->name, 'AsynchronousAsset') . '" async></script>';
        }
    }
}
