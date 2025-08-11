<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\DI;

use BulkGate\Plugin;
use BulkGate\Plugin\Event\Dispatcher;
use BulkGate\PrestaShop\Ajax;
use BulkGate\PrestaShop\Database\Connection;
use BulkGate\PrestaShop\Eshop;
use BulkGate\PrestaShop\Event;
use PrestaShop\PrestaShop\Adapter\Employee\ContextEmployeeProvider;
use PrestaShop\PrestaShop\Adapter\OrderReturnState\OrderReturnStateDataProvider;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\OrderState\OrderStateDataProvider;
use PrestaShop\PrestaShop\Adapter\Shop\Url\BaseUrlProvider;
use Symfony\Component\DependencyInjection\Container as SymfonyContainer;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Factory implements Plugin\DI\Factory
{
	use Plugin\Strict;
	use Plugin\DI\FactoryStatic;

	/**
	 * @param array<string, mixed> $parameters
	 */
	protected static function createContainer(array $parameters = []): Plugin\DI\Container
	{
		/**
		 * @var SymfonyContainer $symfony_di
		 */
		['symfony_di' => $symfony_di] = $parameters;

		$container = new Plugin\DI\Container($parameters['mode'] ?? 'strict');

		$container['prestashop.adapter.shop.context'] = Context::class;
		$container['prestashop.adapter.order.state'] = OrderStateDataProvider::class;
		$container['prestashop.adapter.order.return_state'] = OrderReturnStateDataProvider::class;
		$container['prestashop.adapter.shop.base_url'] = ['factory' => BaseUrlProvider::class, 'factory_method' => fn() => new BaseUrlProvider($symfony_di->get('prestashop.adapter.legacy.context')->getContext()->link)];
		$container['prestashop.adapter.employee.context'] = ['factory' => ContextEmployeeProvider::class, 'factory_method' => fn() => new ContextEmployeeProvider($symfony_di->get('prestashop.adapter.legacy.context')->getContext()->employee)];

		// Database
		$container['database.connection'] = ['factory' => Connection::class, 'parameters' => ['db' => $symfony_di->get('doctrine.dbal.default_connection')]];

		// Debug
		$container['debug.repository.logger'] = ['factory' => Plugin\Debug\Repository\LoggerSettings::class, 'factory_method' => function () use ($container, $parameters): Plugin\Debug\Repository\LoggerSettings {
			$logger = new Plugin\Debug\Repository\LoggerSettings($container->getByClass(Plugin\Settings\Settings::class));
			$logger->setup(is_int($parameters['logger_limit'] ?? null) ? $parameters['logger_limit'] : 100);

			return $logger;
		}];
		$container['debug.logger'] = ['factory' => Plugin\Debug\Logger::class, 'parameters' => ['platform_version' => $parameters['platform_version'], 'module_version' => $parameters['module_version']]];
		$container['debug.requirements'] = Plugin\Debug\Requirements::class;

		// Ajax
		$container['ajax.authenticate'] = Ajax\Authenticate::class;
		$container['ajax.plugin_settings'] = Ajax\PluginSettingsChange::class;

		// Eshop
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new Eshop\Configuration(
			$parameters['module_version'],
			$container->getByClass(BaseUrlProvider::class),
			$container->getByClass(Context::class)
		)];
		$container['eshop.synchronizer'] = Plugin\Eshop\EshopSynchronizer::class;
		$container['eshop.order_status'] = ['factory' => Eshop\OrderStatus::class, 'factory_method' => fn () => new Eshop\OrderStatus(
			$container->getByClass(OrderStateDataProvider::class),
			$container->getByClass(ContextEmployeeProvider::class)
		)];
		$container['eshop.return_status'] = ['factory' => Eshop\ReturnStatus::class, 'factory_method' => fn () => new Eshop\ReturnStatus(
			$container->getByClass(OrderReturnStateDataProvider::class),
			$container->getByClass(ContextEmployeeProvider::class)
		)];
		$container['eshop.language'] = Eshop\Language::class;
		$container['eshop.multistore'] = ['factory' => Eshop\MultiStore::class, 'factory_method' => fn () => new Eshop\MultiStore(
			$container->getByClass(Context::class)
		)];

		// Event loaders
		$container['event.loader.extension'] = ['factory' => Event\Loader\Extension::class, 'auto_wiring' => false];
		$container['event.loader.shop'] = ['factory' => Event\Loader\Shop::class, 'auto_wiring' => false];
		$container['event.loader.order'] = ['factory' => Event\Loader\Order::class, 'auto_wiring' => false];
		$container['event.loader.order_status'] = ['factory' => Event\Loader\OrderStatus::class, 'auto_wiring' => false];
		$container['event.loader.customer'] = ['factory' => Event\Loader\Customer::class, 'auto_wiring' => false];
		$container['event.loader.product'] = ['factory' => Event\Loader\Product::class, 'auto_wiring' => false];
		$container['event.loader.post'] = ['factory' => Event\Loader\Post::class, 'auto_wiring' => false];
		$container['event.loader'] = ['factory' => Plugin\Event\Loader::class, 'factory_method' => fn () => new Plugin\Event\Loader([
			$container->getByClass(Event\Loader\Order::class),
			$container->getByClass(Event\Loader\OrderStatus::class),
			$container->getByClass(Event\Loader\Customer::class),
			$container->getByClass(Event\Loader\Shop::class),
			$container->getByClass(Event\Loader\Product::class),
			$container->getByClass(Event\Loader\Post::class),
			$container->getByClass(Event\Loader\Extension::class),
		])];
		$container['event.dispatcher'] = Dispatcher::class;

		if (in_array($parameters['dispatcher'] ?? null, [Dispatcher::Asset, Dispatcher::Cron, Dispatcher::Direct], true)) {
			Dispatcher::$default_dispatcher = $parameters['dispatcher'];
		}

		// Event
		$container['event.hook'] = ['factory' => Plugin\Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Plugin\Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Plugin\Event\Asynchronous::class;

		// IO
		$container['io.connection.factory'] = ['factory' => Plugin\IO\ConnectionFactory::class, 'factory_method' => function () use ($container): Plugin\IO\ConnectionFactory {
			$configuration = $container->getByClass(Eshop\Configuration::class);

			return new Plugin\IO\ConnectionFactory($configuration->url(), $configuration->product(), $container->getByClass(Plugin\Settings\Settings::class));
		}];
		$container['io.connection'] = ['factory' => Plugin\IO\Connection::class, 'factory_method' => fn () => $container->getByClass(Plugin\IO\ConnectionFactory::class)->create()];
		$container['io.url'] = ['factory' => Plugin\IO\Url::class, 'parameters' => ['url' => $parameters['gate_url'] ?? 'https://portal.bulkgate.com']];

		// Localization
		$iso = $container->getByClass(Eshop\Language::class)->get($symfony_di->get('prestashop.adapter.legacy.context')->getLanguage()->getId()); // pozor! bezi v ruznych kontextech FO a BO
		$container['localization.language'] = ['factory' => Plugin\Localization\LanguageSettings::class, 'parameters' => ['iso' => $iso]];
		$container['localization.translator'] = Plugin\Localization\TranslatorSettings::class;
		$container['localization.formatter'] = extension_loaded('intl') ? ['factory' => Plugin\Localization\FormatterIntl::class, 'factory_method' => fn () => new Plugin\Localization\FormatterIntl($iso)] : Plugin\Localization\FormatterBasic::class;

		// Settings
		$container['settings.repository.database'] = Plugin\Settings\Repository\SettingsDatabase::class;
		$container['settings.repository.synchronizer'] = Plugin\Settings\Repository\SynchronizationDatabase::class;
		$container['settings.settings'] = ['factory' => Plugin\Settings\Settings::class, 'factory_method' => function () use ($container, $parameters): Plugin\Settings\Settings {
			$settings = new Plugin\Settings\Settings($container->getByClass(Plugin\Settings\Repository\SettingsDatabase::class));
			$settings->setDefaultSettings($parameters['default_settings']);

			return $settings;
		}];
		$container['settings.synchronizer'] = Plugin\Settings\Synchronizer::class;

		// User
		$container['user.sign'] = ['factory' => Plugin\User\Sign::class, 'factory_method' => function () use ($container, $parameters): Plugin\User\Sign {
			$sign = new Plugin\User\Sign(
				$container->getByClass(Plugin\Settings\Settings::class),
				$container->getByClass(Plugin\IO\Connection::class),
				$container->getByClass(Plugin\IO\Url::class),
				$container->getByClass(Eshop\Configuration::class),
				$container->getByClass(Plugin\Localization\Language::class),
				$container->getByClass(Plugin\Debug\Logger::class),
			);
			$sign->setDefaultParameters(['referer_id' => $parameters['referer_id'] ?? null]);

			return $sign;
		}];

		return $container;
	}
}
