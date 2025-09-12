<?php

use BulkGate\Plugin;
use BulkGate\PrestaShop\DI\Container;
use BulkGate\PrestaShop\Event\Helpers;
use Twig\Environment as Twig;

if (!defined('_PS_VERSION_')) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class BulkGate_Sms_Module_For_Prestashop extends Module
{
	use Container;

	/**
	 * @var array<array-key, mixed>
	 */
	public $tabs = [
		[
			'name' => BulkGateWhiteLabel . ' SMS',
			'class_name' => 'AdminBulkGateConfigure',
			'parent_class_name' => 'SELL',
			'visible' => true,
			'icon' => 'send',
		],
		[
			'name' => BulkGateWhiteLabel . ' Debug',
			'class_name' => 'AdminBulkGateDebug',
			'parent_class_name' => 'CONFIGURE',
			'visible' => true,
			'icon' => 'bug_report',
		],
	];


	public function __construct()
	{
		$this->name = 'bulkgate_sms_module_for_prestashop';
		$this->tab = 'emailing';
		$this->version = BulkGateModuleVersion;
		$this->author = BulkGateWhiteLabel;
		$this->author_uri = 'https://www.bulkgate.com/';

		parent::__construct();

		$this->ps_versions_compliancy = [
			'min' => BulkGateMinimalPrestashopVersion,
			'max' => _PS_VERSION_,
		];

		$this->displayName = BulkGateWhiteLabel . ' SMS';
		$this->description = $this->l('Send personalized SMS messages that your customers will notice! Prevent important notifications from being overlooked among common channels like email. Use new channels such as SMS, RCS, WhatsApp, and others to stand out and capture your customers\' attention.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
	}


	public function getContent(): void
	{
		Tools::redirectAdmin($this->get('router')->generate('bulkgate_main_app', []));
	}


	public function install(): bool
	{
		$install = parent::install();

		$this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class)->install();

		return $install && $this->installHooks();
	}


	public function uninstall(): bool
	{
		$uninstall = parent::uninstall();

		$this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class)->uninstall();

		return $uninstall;
	}


	/* AdminCustomerSms hooks */

	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderstatuspostupdate
	 */
	public function hookActionOrderStatusPostUpdate(array $params)
	{
		if (!isset($params['id_order']) || !isset($params['newOrderStatus'])) {
			return;
		}

		$order = new Order((int) $params['id_order']);

		$this->runHook('order', 'change-status', new Plugin\Event\Variables([
			'order_status_id' => $params['newOrderStatus']->id,
			'order_id' => (int) $order->id,
			'lang_id' => $order->id_lang,
			'shop_id' => (int) $order->id_shop,
			'customer_id' => $order->id_customer,
		]), ['order' => $order]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionvalidateorder
	 */
	public function hookActionValidateOrder(array $params)
	{
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => (int) $params['order']->id,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
			'customer_id' => (int) $params['order']->id_customer,
		]), ['order' => $params['order']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actioncustomeraccountadd
	 */
	public function hookActionCustomerAccountAdd(array $params)
	{
		if (!isset($params['newCustomer']) || !$params['newCustomer'] instanceof Customer) {
			return;
		}

		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => (int) $params['newCustomer']->id,
			'lang_id' => (int) $params['newCustomer']->id_lang,
			'shop_id' => (int) $params['newCustomer']->id_shop,
		]), ['customer' => $params['newCustomer']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderreturn
	 */
	public function hookActionOrderReturn(array $params)
	{
		if (!isset($params['orderReturn']) || !$params['orderReturn'] instanceof OrderReturn) {
			return;
		}

		$this->runHook('return', 'new', new Plugin\Event\Variables([
			'return_id' => (int) $params['orderReturn']->id,
			'customer_id' => (int) $params['orderReturn']->id_customer,
			'order_id' => (int) $params['orderReturn']->id_order,
			'lang_id' => (int) $params['orderReturn']->getAssociatedLanguage()->id,
			'shop_id' => (int) $params['orderReturn']->getShopId(),
		]));
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionorderslipadd
	 */
	public function hookActionOrderSlipAdd(array $params)
	{
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		$this->runHook('order', 'slip-add', new Plugin\Event\Variables([
			'order_id' => (int) $params['order']->id,
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
			'filter_products' => array_keys(isset($params['qtyList']) ? $params['qtyList'] : []),
		]), ['order' => $params['order']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionadminorderstrackingnumberupdate
	 */
	public function hookActionAdminOrdersTrackingNumberUpdate(array $params)
	{
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		$this->runHook('order', 'tracking-number', new Plugin\Event\Variables([
			'order_id' => (int) $params['order']->id,
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
		]), ['order' => $params['order']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionpaymentconfirmation
	 */
	public function hookActionPaymentConfirmation(array $params)
	{
		if (!isset($params['id_order'])) {
			return;
		}

		$order = new Order($params['id_order']);

		$this->runHook('order', 'payment', new Plugin\Event\Variables([
			'order_id' => (int) $order->id,
			'lang_id' => $order->id_lang,
			'shop_id' => (int) $order->id_shop,
			'customer_id' => $order->id_customer,
		]), ['order' => $order]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionproductdelete
	 */
	public function hookActionProductDelete(array $params)
	{
		if (!isset($params['product']) || !$params['product'] instanceof Product) {
			return;
		}

		$this->runHook('product', 'delete', new Plugin\Event\Variables([
			'shop_id' => (int) $params['product']->id_shop_default,
			'product_id' => (int) $params['product']->id,
		]), ['product' => $params['product']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionupdatequantity
	 */
	public function hookActionUpdateQuantity(array $params)
	{
		if (!isset($params['id_product'])) {
			return;
		}

		if ($params['quantity'] === 0) {
			$this->runHook('product', 'out-of-stock', new Plugin\Event\Variables([
				'shop_id' => $params['id_shop'],
				'product_id' => $params['id_product'],
				'id_product_attribute' => $params['id_product_attribute'],
			]));
		} else {
			$this->runHook('product', 'update-quantity', new Plugin\Event\Variables([
				'shop_id' => $params['id_shop'],
				'product_id' => $params['id_product'],
				'id_product_attribute' => $params['id_product_attribute'],
			]));
		}
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionproductcancel
	 */
	public function hookActionProductCancel(array $params)
	{
		if (!isset($params['order']) || !$params['order'] instanceof Order) {
			return;
		}

		// tento hook se spousti ze 4 ruznych mist, viz CancellationActionType.  i v pripade hookActionOrderSlipAdd (kdyz castecne vratim produkt)
		$this->runHook('order', 'product-cancel', new Plugin\Event\Variables([
			'order_id' => (int) $params['order']->id,
			'filter_products' => [$params['id_order_detail']],
			'customer_id' => (int) $params['order']->id_customer,
			'lang_id' => (int) $params['order']->id_lang,
			'shop_id' => (int) $params['order']->id_shop,
		]), ['order' => $params['order']]);
	}


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/actionemailsendbefore
	 */
	public function hookActionEmailSendBefore(array $params)
	{
		if (!isset($params['templateVars']) || !isset($params['template']) || $params['template'] !== 'contact') {
			return;
		}

		$customer_message = $params['templateVars']['{message}'] ?? null;

		if ($customer_message !== null) {
			$this->runHook('contact', 'form', new Plugin\Event\Variables([
				'customer_email' => $params['templateVars']['{email}'] ?? null,
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


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 */
	public function hookActionPrestaSmsSendSms(array $params)
	{
		$number = $params['number'] ?? null;
		$template = $params['template'] ?? null;
		$variables = $params['variables'] ?? [];
		$settings = $params['settings'] ?? [];

		$hook = $this->getBulkGateContainer()->getByClass(Plugin\Event\Hook::class);

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


	/**
	 * @param array<array-key, mixed> $params
	 * @return void
	 */
	public function hookActionPrestaSmsExtendsVariables(array $params)
	{
	}


	/* BackOffice hooks */


	/**
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/displayadminorderside
	 * @param array<array-key, mixed> $params
	 */
	public function hookDisplayAdminOrderSide(array $params): string
	{
		['id_order' => $id] = $params;

		$settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

		if (!$settings->load('static:application_token')) {
			return '';
		}

		$order = new Order($id);

		$this->getBulkGateContainer()
			->getByClass(Plugin\Event\Loader::class)
			->load($variables = new Plugin\Event\Variables([
				'order_id' => $order->id,
				'shop_id' => $order->id_shop,
				'customer_id' => $order->id_customer,
				'lang_id' => $order->id_lang,
				'order_status_id' => $order->current_state,
			])
			);

		return $this->render($this->getModuleTemplatePath() . 'send-message.html.twig', [
			'token' => $this->getBulkGateContainer()->getByClass(Plugin\User\Sign::class)->authenticate(),
			'url' => $this->getBulkGateContainer()->getByClass(Plugin\IO\Url::class),
			'variables' => array_merge(
				$variables->toArray(),
				[
					// these variables are for web component
					'first_name' => Helpers::priorityValues(['customer_firstname', 'customer_invoice_firstname'], $variables),
					'last_name' => Helpers::priorityValues(['customer_lastname', 'customer_invoice_lastname'], $variables),
					'phone_mobile' => Helpers::priorityValues(['customer_mobile', 'customer_phone', 'customer_invoice_mobile', 'customer_invoice_phone'], $variables),
					'phone_number_iso' => Helpers::priorityValues(['customer_country_id', 'customer_invoice_country_id'], $variables),
				]
			),
		]);
	}

	/**
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks
	 * @return void
	 */
	public function hookActionListModules()
	{
		$settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

		if ($settings->load('static:application_token') === null) {
			$this->warning = 'You must be logged in to BulkGate to start sending SMS!';
		}
	}


	/**
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/displaybackofficeheader
	 * @return string
	 */
	public function hookDisplayBackOfficeHeader()
	{
		return $this->asynchronousAsset();
	}

	/* FrontOffice hooks */

	/**
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/displayheader
	 * @return string
	 */
	public function hookDisplayHeader()
	{
		return $this->asynchronousAsset();
	}


	/**
	 * @see https://devdocs.prestashop-project.org/8/modules/concepts/hooks/list-of-hooks/additionalcustomerformfields
	 * @param array<array-key, mixed> $params
	 * @return array<FormField>|null
	 */
	public function hookAdditionalCustomerFormFields(array $params)
	{
		$settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

		if (!$settings->load('main:marketing_message_opt_in_enabled')) {
			return null;
		}

		$url = $settings->load('main:marketing_message_opt_in_url');
		$label_suffix = $url && !preg_match('~^https?://$~', $url) ? '[1][2]%url%[/2]' : '';

		$label = $this->trans(
			'I consent to receiving marketing communications via SMS, Viber, RCS, WhatsApp, and other similar channels.' . $label_suffix,
			[
				'_raw' => true,
				'[1]' => '<br>',
				'[2]' => '<a href="' . $url . '" target="_blank">',
				'%url%' => Tools::htmlentitiesUTF8($url),
				'[/2]' => '</a>',
			]
		);

		return [
			(new FormField())
				->setName('bulkgate_marketing_message_opt_in')
				->setType('checkbox')
				->setValue($settings->load('main:marketing_message_opt_in_default'))
				->setLabel($settings->load('main:marketing_message_opt_in_label') ?: $label),
		];
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
		$this->registerHook('actionProductCancel');
		$this->registerHook('actionEmailSendBefore');
		$this->registerHook('actionPrestaSmsSendSms');
		$this->registerHook('actionPrestaSmsExtendsVariables');

		return true;
	}


	private function installFrontOfficeHooks(): bool
	{
		$this->registerHook('displayHeader');
		$this->registerHook('additionalCustomerFormFields');

		return true;
	}


	private function installBackOfficeHooks(): bool
	{
		$this->registerHook('displayAdminOrderSide');
		$this->registerHook('displayBackOfficeHeader');
		$this->registerHook('actionListModules');

		return true;
	}

	/**
	 * Render a twig template.
	 */
	private function render(string $template, array $params = []): string
	{
		/**
		 * @var Twig|false $twig
		 */
		$twig = $this->get('twig');

		if ($twig)
		{
			return $twig->render($template, $params);
		}
		return '';
	}

	/**
	 * Get path to this module's template directory
	 */
	private function getModuleTemplatePath(): string
	{
		return sprintf('@Modules/%s/views/templates/admin/', $this->name);
	}


	/**
	 * @param array<array-key, mixed> $parameters
	 * @param (callable(): mixed)|null $success_callback
	 */
	private function runHook(string $category, string $endpoint, Plugin\Event\Variables $variables, array $parameters = [], ?callable $success_callback = null): void
	{
		try
		{
			$this->getBulkGateContainer()->getByClass(Plugin\Event\Dispatcher::class)->dispatch($category, $endpoint, $variables, $parameters, $success_callback);
		}
		catch (Plugin\Exception $e)
		{
		}
	}


	private function asynchronousAsset(): string
	{
		$settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

		if (in_array($settings->load('main:dispatcher'), [Plugin\Event\Dispatcher::Asset, Plugin\Event\Dispatcher::Cron])) {
			return '<script type="text/javascript" src="' . $this->context->link->getModuleLink($this->name, 'AsynchronousAsset') . '" async></script>';
		}

		return '';
	}
}
