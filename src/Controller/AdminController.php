<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Controller;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @see https://www.bulkgate.com/
 */

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use BulkGate\{Plugin\Debug\Logger, Plugin\Debug\Requirements, Plugin\Eshop, Plugin\IO\Url, Plugin\Settings, Plugin\User\Sign, Plugin\Utils\Json, PrestaSms\Ajax\Authenticate, PrestaSms\Ajax\PluginSettingsChange};
use Symfony\Component\{HttpFoundation\JsonResponse, HttpFoundation\Request, HttpFoundation\Response, Routing\Generator\UrlGeneratorInterface};

class AdminController extends FrameworkBundleAdminController
{
	public function indexAction(Sign $sign, Url $url, Settings\Synchronizer $settings_synchronizer, Eshop\EshopSynchronizer $shop_synchronizer, Settings\Settings $settings)
	{
		$shop_synchronizer->run();

		$token = $sign->authenticate(false, ['expire' => time() + 300]);

		return $this->render('@Modules/bg_prestasms/views/templates/admin/index.html.twig', [
			'layoutTitle' => 'BulkGate SMS',
			'showContentHeader' => false,
			'token' => $token,
			'url' => $url,
			'synchronizer' => $settings_synchronizer,
			'settings' => $settings,
		]);
	}


	public function debugAction(Request $request, Requirements $requirements, Url $url, Logger $logger): Response
	{
		$requirements = $requirements->run([
			$requirements->same('{"message":"BulkGate API"}', file_get_contents($url->get('api/welcome')), 'Api Connection'),
			$requirements->same(true, version_compare(_PS_VERSION_, '1.7.5', '>='), 'Prestashop ver. >= 1.7.5'),
		]);

		return $this->render('@Modules/bg_prestasms/views/templates/admin/debug.html.twig', [
			'layoutTitle' => 'BulkGate SMS - debug',
			'php_version' => phpversion(),
			'prestashop_version' => _PS_VERSION_,
			'url' => $url->get(),
			'requirements' => $requirements,
			'errors' => array_reverse($logger->getList()),
		]);
	}


	public function proxyAction(string $action, Request $request, PluginSettingsChange $settings_change, Authenticate $authenticate, Sign $sign, UrlGeneratorInterface $router): JsonResponse
	{
		$base_url = $request->getSchemeAndHttpHost();

		if ($action === 'login')
		{
			['email' => $email, 'password' => $password] = Json::decode($request->getContent());

			return $this->json($sign->in($email, $password, $base_url . $router->generate('bulkgate_main_app', [
				'reload' => time(),
				'_fragment' => '/dashboard'
			], UrlGeneratorInterface::ABSOLUTE_PATH)));
		}
		else if ($action === 'logout')
		{
			return $this->json($sign->out('/sign/in'));
		}
		else if ($action === 'authenticate')
		{
			return $this->json($authenticate->run('/sign/in'));
		}
		else if ($action === 'module-settings')
		{
			$data = Json::decode($request->getContent());

			return $this->json($settings_change->run($data, fn(string $lang): string => $base_url . $router->generate('bulkgate_main_app', [
				'reload' => $lang,
				'_fragment' => '/dashboard'
			], UrlGeneratorInterface::ABSOLUTE_PATH)));
		}
		else
		{
			return $this->json([
				'status' => 'error',
				'message' => 'Unknown action',
			]);
		}
	}
}
