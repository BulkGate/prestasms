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
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
        ];

        $this->displayName = 'PrestaSMS';
		//Posílejte personalizované SMS zprávy, kterých si zákazník všimne! Zabraňte nepovšimnutí si důležitých notifikací mezi běžnými kanály jako email. Používejte nové kanály, jako SMS, RCS, Whatsapp a další které zajistí odlišnost a získají si pozornost zákazníků
        $this->description = $this->l('Extend your PrestaShop store capabilities. Send personalized bulk SMS messages. Notify your customers about order status via customer SMS notifications. Receive order updates via Admin SMS notifications.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        PrestaSms\DI\Factory::setup(fn () => ['db' => $this->get('doctrine.dbal.default_connection')]);
    }

    public function getContent(): void
    {
        // we have dedicated controller.
        Tools::redirectAdmin($this->get('router')->generate('bulkgate_main_app', []));
    }

    public function install(): bool
    {
        $install = parent::install();

        PrestaSms\DI\Factory::get()->getByClass(Settings::class)->install();

        return $install && $this->installHooks();
    }

    public function uninstall()
    {
        $uninstall = parent::uninstall();

        PrestaSms\DI\Factory::get()->getByClass(Settings::class)->uninstall();

        return $uninstall;
    }

    private function installHooks(): bool
    {
        $this->installAdminCustomerSmsHooks();
		$this->installBackOfficeHooks();
		$this->installFrontOfficeHooks();

		return true;
    }

	private function installAdminCustomerSmsHooks(): bool
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

		return true;
	}

	private function installFrontOfficeHooks(): bool
	{
		$this->registerHook('displayHeader');

		return true;
	}

	private function installBackOfficeHooks(): bool
	{
		$this->registerHook('displayAdminOrderSide');
		$this->registerHook('displayBackOfficeHeader');
		$this->registerHook('actionListModules');

		return true;
	}

	/* AdminCustomerSms hooks */

    /** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderstatuspostupdate */
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        if (!isset($params['id_order']) || !isset($params['newOrderStatus'])) {
            return;
        }

		$order = new Order((int) $params['id_order']);

		$this->runHook('order', 'change-status', new Variables([
			'order_status_id' => $params['newOrderStatus']->id,
			'order_id' => (int) $order->id,
			'lang_id' => (int) $order->id_lang,
			'shop_id' => (int) $order->id_shop,
			'customer_id' => (int) $order->id_customer,
		]), ['order' => $order]);
    }

	public function testHookActionOrderStatusPostUpdate()
	{
		$newOrderStatus = new \OrderState(2);

		//should invoke
		$this->hookActionOrderStatusPostUpdate(['id_order' => 7, 'newOrderStatus' => $newOrderStatus]);

		//should not invoke
		$this->hookActionOrderStatusPostUpdate(['id_order' => 7]);
		$this->hookActionOrderStatusPostUpdate(['newOrderStatus' => $newOrderStatus]);
		$this->hookActionOrderStatusPostUpdate([]);
	}

    /** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionvalidateorder */
    public function hookActionValidateOrder(array $params)
    {
        if (!isset($params['order']) || !$params['order'] instanceof Order) {
            return;
        }

		$this->runHook('order', 'new', new Variables([
			'order_id' => (int) $params['order']->id,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
			'customer_id' => (int) $params['order']->id_customer,
		]), ['order' => $params['order']]);
    }


	private function testHookActionValidateOrder()
	{
		$order = new Order(8); // 7 - CZC | 8 - Alza

		//should invoke
		$this->hookActionValidateOrder(['order' => $order]);

		//should not invoke
		$this->hookActionValidateOrder(['order' => new stdClass]);
		$this->hookActionValidateOrder([]);
	}

    /** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actioncustomeraccountadd */
    public function hookActionCustomerAccountAdd(array $params)
    {
        if (!isset($params['newCustomer']) || !$params['newCustomer'] instanceof Customer) {
            return;
        }

		$this->runHook('customer', 'new', new Variables([
			'customer_id' => (int) $params['newCustomer']->id,
			'lang_id' => (int) $params['newCustomer']->id_lang,
			'shop_id' => (int) $params['newCustomer']->id_shop,
		]), ['customer' => $params['newCustomer']]);
    }

	private function testHookActionCustomerAccountAdd()
	{
		$customer = new Customer(5); // 2 - CZC | 5 - Alza

		//should invoke
		$this->hookActionCustomerAccountAdd(['newCustomer' => $customer]);

		//should not invoke
		$this->hookActionCustomerAccountAdd(['newCustomer' => new stdClass]);
		$this->hookActionCustomerAccountAdd([]);
	}

	/** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderreturn */
    public function hookActionOrderReturn(array $params)
    {
        if (!isset($params['orderReturn']) || !$params['orderReturn'] instanceof OrderReturn) {
            return;
        }

		$this->runHook('return', 'new', new Variables([
			'return_id' => (int) $params['orderReturn']->id,
			'customer_id' => (int) $params['orderReturn']->id_customer,
			'order_id' => (int) $params['orderReturn']->id_order,
			'lang_id' => (int) $params['orderReturn']->getAssociatedLanguage()->id,
			'shop_id' => (int) $params['orderReturn']->getShopId(),
		]));
    }

	public function testHookActionOrderReturn()
	{
		$order_return = new OrderReturn(1);

		//should invoke
		$this->hookActionOrderReturn(['orderReturn' => $order_return]);

		//should not invoke
		$this->hookActionOrderReturn(['orderReturn' => new stdClass]);
		$this->hookActionOrderReturn([]);
	}

    /** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderslipadd */
    public function hookActionOrderSlipAdd(array $params)
    {
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		$this->runHook('order', 'TODO_slip_add', new Variables([
			'order_id' => (int) $params['order']->id,
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
			'filter_products' => array_keys(isset($params['qtyList']) ? $params['qtyList'] : []),
		]), ['order' => $params['order']]);
    }

	public function testHookActionOrderSlipAdd()
	{
		$order = new Order(2); // 7 - CZC | 8 - Alza

		//should invoke
		$this->hookActionOrderSlipAdd(['order' => $order, 'qtyList' => [3 => 1]]);
		$this->hookActionOrderSlipAdd(['order' => $order, 'qtyList' => [3 => 1, 4 => 1]]);

		//should not invoke
		$this->hookActionOrderSlipAdd(['order' => new stdClass(), 'qtyList' => [3 => 1, 4 => 1]]);
		$this->hookActionOrderSlipAdd(['order' => new stdClass()]);
		$this->hookActionOrderSlipAdd([]);
	}

    /** https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionadminorderstrackingnumberupdate */
    public function hookActionAdminOrdersTrackingNumberUpdate(array $params)
    {
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		$this->runHook('order', 'tracking-number', new Variables([
			'order_id' => (int) $params['order']->id,
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
		]), ['order' => $params['order']]);
    }

	public function testHookActionAdminOrdersTrackingNumberPostUpdate()
	{
		$order = new Order(2); // 7 - CZC | 8 - Alza

		//should invoke
		$this->hookActionAdminOrdersTrackingNumberUpdate(['order' => $order]);

		//should not invoke
		$this->hookActionAdminOrdersTrackingNumberUpdate(['order' => new stdClass]);
		$this->hookActionAdminOrdersTrackingNumberUpdate([]);
	}

    /** https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionpaymentconfirmation */
    public function hookActionPaymentConfirmation(array $params)
    {
		if (!isset($params['id_order'])) {
			return;
		}

		$order = new Order($params['id_order']);

		$this->runHook('order', 'payment', new Variables([
			'order_id' => (int) $order->id,
			'lang_id' => (int) $order->id_lang,
			'shop_id' => (int) $order->id_shop,
			'customer_id' => (int) $order->id_customer,
		]), ['order' => $order]);
    }

	public function testHookActionPaymentConfirmation()
	{
		//should invoke
		$this->hookActionPaymentConfirmation(['id_order' => 2]);

		//should not invoke
		$this->hookActionPaymentConfirmation([]);
	}

	/** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionproductdelete */
    public function hookActionProductDelete(array $params)
    {
		if (!isset($params['product']) || !$params['product'] instanceof Product) {
			return;
		}

		$this->runHook('product', 'TODO_delete', new Variables([
			'shop_id' => (int) $params['product']->id_shop_default,
			'product_id' => (int) $params['product']->id,
		]), ['product' => $params['product']]);
    }

	public function testHookActionProductDelete()
	{
		$product = new Product(4);

		//should invoke
		$this->hookActionProductDelete(['product' => $product]);

		//should not invoke
		$this->hookActionProductDelete(['product' => new stdClass]);
		$this->hookActionProductDelete([]);
	}

    /** @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionupdatequantity */
	public function hookActionUpdateQuantity(array $params)
    {
		if (!isset($params['id_product'])) {
			return;
		}

		if ($params['quantity'] === 0) {
			$this->runHook('product', 'out-of-stock', new Variables([
				'shop_id' => $params['id_shop'],
				'product_id' => $params['id_product'],
				'id_product_attribute' => $params['id_product_attribute'],
			]));
		}
    }

	private function testHookActionUpdateQuantity()
	{
		//should invoke
		$this->hookActionUpdateQuantity(['id_shop' => 2, 'id_product' => 19, 'quantity' => 0, 'id_product_attribute' => 0]);

		//should not invoke
		$this->hookActionUpdateQuantity(['id_shop' => 2, 'quantity' => 5, 'id_product_attribute' => 0]);
		$this->hookActionUpdateQuantity(['id_shop' => 2, 'id_product' => 19, 'quantity' => 5, 'id_product_attribute' => 0]);
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
                        'shop_id' => (int) $params['product']->id_shop_default,
                        'product_id' => (int) $params['product']->id,
                    ]), ['product' => $params['product']]);
                }
            }
        }
    }*/

    /** https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionproductcancel */
    public function hookActionProductCancel(array $params)
    {
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		//todo: tento hook se spousti ze 4 ruznych mist, viz CancellationActionType.  i v pripade hookActionOrderSlipAdd (kdyz castecne vratim produkt)
		$this->runHook('order', 'TODO_product_cancel', new Variables([
			'order_id' => (int) $params['order']->id,
			'filter_products' => [$params['id_order_detail']],
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
		]), ['order' => $params['order']]);
    }

	public function testHookActionProductCancel()
	{
		$order = new Order(2);

		//should invoke
		$this->hookActionProductCancel(['order' => $order, 'id_order_detail' => 3]);

		//should not invoke
		$this->hookActionProductCancel(['order' => new stdClass, 'id_order_detail' => 3]);
		$this->hookActionProductCancel(['id_order_detail' => 3]);
	}

	/** https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionemailsendbefore */
    public function hookActionEmailSendBefore(array $params)
    {
		if (!isset($params['templateVars']) || !isset($params['template']) || $params['template'] !== 'contact') {
            return;
        }

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
				'shop_id' => isset($params['idShop']) ? (int) $params['idShop'] : null,
			]));
		}
    }

	public function testHookActionEmailSendBefore()
	{
		//should invoke
		$this->hookActionEmailSendBefore(['templateVars' => ['{message}' => 'Hello world', '{email}' => 'john.doe@example.com'], 'template' => 'contact']);
		$this->hookActionEmailSendBefore(['templateVars' => ['{message}' => 'Hello world'], 'template' => 'contact']);

		//should not invoke
		$this->hookActionEmailSendBefore(['templateVars' => [], 'template' => 'contact']);
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

	public function hookActionPrestaSmsExtendsVariables(array $params)
	{
	}

	/* BackOffice hooks */

    public function hookDisplayAdminOrderSide(array $params)
    {
        ['id_order' => $id] = $params;

        $settings = $this->get('bulkgate.plugin.settings.settings');
        $sign = $this->get('bulkgate.plugin.user.sign');
        $url = $this->get('bulkgate.plugin.io.url');
		$loader = $this->get('bulkgate.plugin.event.loader');

		$order = new \Order($id);
		$variables = new Variables([
			'order_id' => $order->id,
			'customer_id' => $order->id_customer,
			'lang_id' => $order->id_lang,
			'order_status_id' => $order->current_state
		]);
		$loader->load($variables);

        $token = $sign->authenticate();

        if ($settings->load('static:application_token', false)) { // todo: isModuleLoggedIn
            return $this->render($this->getModuleTemplatePath() . 'send-message.html.twig', [
                'token' => $token,
                'url' => $url,
				'variables' => [
					...$variables->toArray(),
					// these variables are for web component
					'first_name' => PrestaSms\Event\Helpers::priorityValues(['customer_firstname', 'customer_invoice_firstname'], $variables),
					'last_name' => PrestaSms\Event\Helpers::priorityValues(['customer_lastname', 'customer_invoice_lastname'], $variables),
					'phone_mobile' => PrestaSms\Event\Helpers::priorityValues(['customer_mobile', 'customer_phone', 'customer_invoice_mobile', 'customer_invoice_phone'], $variables),
					'phone_number_iso' => PrestaSms\Event\Helpers::priorityValues(['customer_country_id', 'customer_invoice_country_id'], $variables)
				],
            ]);
        }

        return null;
    }

	public function hookActionListModules()
	{
		$settings = $this->get('bulkgate.plugin.settings.settings');

		if ($settings->load('static:application_token') === null) {
			$this->warning = 'You must be logged in to BulkGate to start sending SMS!';
		}
	}

	public function hookDisplayBackOfficeHeader()
	{
		//$this->test();
		return $this->asynchronousAsset();
	}

	/* FrontOffice hooks */

    public function hookDisplayHeader()
    {
        return $this->asynchronousAsset();
    }

	private function test()
	{
		$this->testHookActionOrderStatusPostUpdate();
		$this->testHookActionValidateOrder();
		$this->testHookActionCustomerAccountAdd();
		$this->testHookActionOrderReturn();
		$this->testHookActionOrderSlipAdd();
		$this->testHookActionAdminOrdersTrackingNumberPostUpdate();
		$this->testHookActionPaymentConfirmation();
		$this->testHookActionProductDelete();
		$this->testHookActionUpdateQuantity();
		$this->testHookActionProductCancel();
		$this->testHookActionEmailSendBefore();
	}

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
        return sprintf('@Modules/%s/views/templates/admin/', $this->name);
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
