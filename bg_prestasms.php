<?php

use BulkGate\PrestaSms, BulkGate\Extensions;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/src/init.php';

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Bg_PrestaSms extends Module
{
    /** @var PrestaSms\DIContainer */
    private $ps_di;

    /** @var Extensions\Settings */
    private $ps_settings;

    /** @var Extensions\Translator */
    private $ps_translator;

    public function __construct()
    {
        $this->name = _BG_PRESTASMS_SLUG_;
        $this->version = _BG_PRESTASMS_VERSION_;
        $this->author = _BG_PRESTASMS_AUTHOR_;
        $this->tab = 'emailing';
        $this->author_uri = _BG_PRESTASMS_AUTHOR_URL_;
        $this->ps_versions_compliancy = [
            'min' => _BG_PRESTASMS_PS_MIN_VERSION_,
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->ps_di = new PrestaSms\DIContainer(\Db::getInstance());
        $this->ps_settings = $this->ps_di->getSettings();
        $this->ps_translator = $this->ps_di->getTranslator();

        $this->displayName = _BG_PRESTASMS_NAME_;
        $this->description = $this->l('Extend your PrestaShop store capabilities. Send personalized bulk SMS messages. Notify your customers about order status via customer SMS notifications. Receive order updates via Admin SMS notifications.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }


    public function getContent()
    {
        // we have dedicated controller.
        \Tools::redirectAdmin($this->context->link->getAdminLink('AdminPrestaSms'));
    }


    public function install()
    {
        $install = parent::install();
        $this->ps_settings->install();
        PrestaSms\Helpers::installMenu($this->ps_translator);
        $this->installHooks();

        return $install;
    }


    public function uninstall()
    {
        $uninstall = parent::uninstall();
        $this->ps_settings->uninstall();
        PrestaSms\Helpers::uninstallMenu();

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
        $this->registerHook('displayAdminOrderRight');
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
                if(Extensions\Helpers::outOfStockCheck($this->ps_settings, (int) $params['product']->id))
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
        $this->ps_di->getConnection()->run(
            new BulkGate\Extensions\IO\Request(
                $this->ps_di->getModule()->getUrl('/module/hook/custom'),
                array(
                    'number' => isset($params['number']) ? $params['number'] : null,
                    'template' => isset($params['template']) ? $params['template'] : null,
                    'variables' => isset($params['variables']) && is_array($params['variables']) ? $params['variables'] : array(),
                    'settings' => isset($params['settings']) && is_array($params['settings']) ? $params['settings'] : array()
                ),
                true)
        );
    }


    public function hookDisplayAdminOrderRight(array $params)
    {
        if($this->ps_settings->load("static:application_token", false))
        {
            if(isset($params['id_order']))
            {
                list($phone_number, $iso) = PrestaSms\Helpers::getOrderPhoneNumber($params['id_order']);
            }
            else
            {
                $phone_number = $iso = null;
            }

            $controller = 'AdminPrestaSmsDashboardDefault';

            try 
            {
                $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeHtml', array('BulkGate\Extensions\Escape', 'html'));
                $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeJs', array('BulkGate\Extensions\Escape', 'js'));
                $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeUrl', array('BulkGate\Extensions\Escape', 'url'));
                $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeHtmlAttr', array('BulkGate\Extensions\Escape', 'htmlAttr'));
                $this->context->smarty->registerPlugin('modifier', 'prestaSmsTranslate', array($this->ps_di->getTranslator(), 'translate'));
            }
            catch (SmartyException $e)
            {
            }

            return $this->context->smarty->createTemplate(BULKGATE_PLUGIN_DIR.'/templates/panel.tpl', null, null, array(
                'application_id' => $this->ps_settings->load('static:application_id', ''),
                'language' => $this->ps_settings->load('main:language', 'en'),
                'id' => $phone_number,
                'key' => $iso,
                'presenter' => 'ModuleComponents',
                'action' => 'sendSms',
                'mode' => defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist',
                'widget_api_url' => $this->ps_di->getModule()->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/widget-api/widget-api.js'),
                'logo' => $this->ps_di->getModule()->getUrl('/images/products/ps.svg'),
                'proxy' => array(),
                'salt' => Extensions\Compress::compress(PrestaSms\Helpers::generateTokens()),
                'authenticate' => array(
                    'ajax' => true,
                    'controller' => $controller,
                    'action' => 'authenticate',
                    'token'  => \Tools::getAdminTokenLite($controller),
                ),
                'homepage' => $this->context->link->getAdminLink('AdminPrestaSmsDashboardDefault'),
                'info' => $this->ps_di->getModule()->info()
            ))->fetch();
        }
        return '';
    }


    public function hookActionPrestaSmsExtendsVariables(array $params)
    {
    }


    public function runHook($name, Extensions\Hook\Variables $variables)
    {
        $hook = new Extensions\Hook\Hook(
            $this->ps_di->getModule()->getUrl('/module/hook'),
            $variables->get('lang_id', (int) $this->context->language->id),
            $variables->get('store_id', (int) $this->context->shop->id),
            $this->ps_di->getConnection(),
            $this->ps_settings,
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
