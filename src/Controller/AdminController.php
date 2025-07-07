<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Controller;

use BulkGate\Plugin;
use BulkGate\PrestaShop\Ajax;
use BulkGate\PrestaShop\DI\Container;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class AdminController extends FrameworkBundleAdminController
{
    use Container;

    public function indexAction(): Response
    {
        $sign = $this->getBulkGateContainer()->getByClass(Plugin\User\Sign::class);
        $url = $this->getBulkGateContainer()->getByClass(Plugin\IO\Url::class);
        $settings_synchronizer = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Synchronizer::class);
        $shop_synchronizer = $this->getBulkGateContainer()->getByClass(Plugin\Eshop\EshopSynchronizer::class);
        $settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

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

    public function debugAction(): Response
    {
        $requirements = $this->getBulkGateContainer()->getByClass(Plugin\Debug\Requirements::class);
        $url = $this->getBulkGateContainer()->getByClass(Plugin\IO\Url::class);
        $logger = $this->getBulkGateContainer()->getByClass(Plugin\Debug\Logger::class);

        $requirements = $requirements->run([
            $requirements->same('{"message":"BulkGate API"}', file_get_contents($url->get('api/welcome')), 'Api Connection'),
            $requirements->same(true, version_compare($logger->platform_version, BulkGateMinimalPrestashopVersion, '>='), 'Prestashop ver. >= ' . BulkGateMinimalPrestashopVersion),
        ]);

        return $this->render('@Modules/bg_prestasms/views/templates/admin/debug.html.twig', [
            'layoutTitle' => 'BulkGate SMS - debug',
            'php_version' => phpversion(),
            'prestashop_version' => _PS_VERSION_,
            'platform_version' => $logger->platform_version,
            'module_version' => $logger->module_version,
            'url' => $url->get(),
            'requirements' => $requirements,
            'errors' => array_reverse($logger->getList()),
        ]);
    }

    public function proxyAction(string $action, Request $request, UrlGeneratorInterface $router): JsonResponse
    {
        $settings_change = $this->getBulkGateContainer()->getByClass(Ajax\PluginSettingsChange::class);
        $authenticate = $this->getBulkGateContainer()->getByClass(Ajax\Authenticate::class);
        $sign = $this->getBulkGateContainer()->getByClass(Plugin\User\Sign::class);

        $base_url = $request->getSchemeAndHttpHost();

        if ($action === 'login') {
            ['email' => $email, 'password' => $password] = Plugin\Utils\Json::decode((string) $request->getContent());

            return $this->json($sign->in($email, $password, $base_url . $router->generate('bulkgate_main_app', [
                'reload' => time(),
                '_fragment' => '/dashboard',
            ], UrlGeneratorInterface::ABSOLUTE_PATH)));
        } elseif ($action === 'logout') {
            return $this->json($sign->out('/sign/in'));
        } elseif ($action === 'authenticate') {
            return $this->json($authenticate->run('/sign/in'));
        } elseif ($action === 'module-settings') {
            $data = Plugin\Utils\Json::decode((string) $request->getContent());

            return $this->json($settings_change->run($data, fn (string $lang): string => $base_url . $router->generate('bulkgate_main_app', [
                'reload' => $lang,
                '_fragment' => '/dashboard',
            ], UrlGeneratorInterface::ABSOLUTE_PATH)));
        } else {
            return $this->json([
                'status' => 'error',
                'message' => 'Unknown action',
            ]);
        }
    }
}
