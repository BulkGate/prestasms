<?php

namespace BulkGate\PrestaSms\Controller;

use BulkGate\Plugin\Debug\Logger;
use BulkGate\Plugin\Debug\Requirements;
use BulkGate\Plugin\IO\Url;
use BulkGate\Plugin\Settings;
use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\User\Sign;
use BulkGate\Plugin\Utils\Json;
use BulkGate\PrestaSms\Ajax\Authenticate;
use BulkGate\PrestaSms\Ajax\PluginSettingsChange;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminController extends FrameworkBundleAdminController
{
    public function indexAction(Sign $sign, Url $url, Settings\Synchronizer $settings_synchronizer, Eshop\EshopSynchronizer $shop_synchronizer, Settings\Settings $settings)
    {
        $shop_synchronizer->run();

        $token = $sign->authenticate(false, ['expire' => time() + 300]);

        return $this->render('@Modules/bg_prestasms/views/index.html.twig', [
            'layoutTitle' => 'BulkGate SMS',
            'showContentHeader' => false,
            'token' => $token,
            'url' => $url,
            'synchronizer' => $settings_synchronizer,
            'settings' => $settings,
        ]);
    }

    public function debugAction(Request $request, Requirements $requirements, Logger $logger)
    {
        $requirements = $requirements->run([
            $requirements->same('{"message":"BulkGate API"}', file_get_contents('https://portal.bulkgate.com/api/welcome'), 'Api Connection'),
            $requirements->same(true, version_compare(_PS_VERSION_, '1.7.5', '>='), 'Prestashop ver. >= 1.7.5'),
        ]);

        return $this->render('@Modules/bg_prestasms/views/debug.html.twig', [
            'layoutTitle' => 'BulkGate SMS - debug',
            'php_version' => phpversion(),
            'prestashop_version' => _PS_VERSION_,
			'requirements' => $requirements,
			'errors' => array_reverse($logger->getList()),
        ]);
    }

    public function proxyAction(string $action, Request $request, PluginSettingsChange $settings_change, Authenticate $authenticate, Sign $sign): JsonResponse
    {
        switch($action)
        {
            case "login":
                ['email' => $email, 'password' => $password] = Json::decode($request->getContent());

                return $this->json($sign->in($email, $password, '/dashboard'));
            case "logout":
                return $this->json($sign->out('/sign/in'));
            case "authenticate":
                return $this->json($authenticate->run('/sign/in'));
            case "module-settings":
                $data = Json::decode($request->getContent());

                return $this->json($settings_change->run($data));
        }
    }
}
