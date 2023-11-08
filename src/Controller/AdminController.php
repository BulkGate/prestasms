<?php

namespace BulkGate\PrestaSms\Controller;

use BulkGate\Plugin\IO\Url;
use BulkGate\Plugin\Settings\Settings;
use BulkGate\Plugin\Settings\Synchronizer;
use BulkGate\Plugin\User\Sign;
use BulkGate\Plugin\Utils\Json;
use BulkGate\PrestaSms\Ajax\Authenticate;
use BulkGate\PrestaSms\Ajax\PluginSettingsChange;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminController extends FrameworkBundleAdminController
{
    public function __construct()
    {

    }

    public function indexAction(Sign $sign, Url $url, Synchronizer $synchronizer, Settings $settings)
    {
        $token = $sign->authenticate(false, ['expire' => time() + 300]);

        return $this->render('@Modules/bg_prestasms/views/index.html.twig', [
            'layoutTitle' => 'BulkGate SMS',
            'showContentHeader' => false,
            'token' => $token,
            'url' => $url,
            'synchronizer' => $synchronizer,
            'settings' => $settings,
        ]);
    }

    public function debugAction(Request $request)
    {
        /*$requirements = $requirements->run([
            $requirements->same('{"message":"BulkGate API"}', file_get_contents('https://portal.bulkgate.com/api/welcome'), 'Api Connection'),
            $requirements->same(true, version_compare(_PS_VERSION_, '1.7.5', '>='), 'WordPress ver. >= 1.7.5'),
        ]);*/

        return $this->render('@Modules/bg_prestasms/views/debug.html.twig', [
            'layoutTitle' => 'BulkGate SMS - debug',
            'php_version' => phpversion(),
            'prestashop_version' => _PS_VERSION_,
        ]);
    }

    public function proxyAction(string $action, Request $request, PluginSettingsChange $settings_change, Authenticate $authenticate): JsonResponse
    {
        switch($action)
        {
            case "login":
                // https://github.com/PrestaShop/PrestaShop/issues/18703 - z nejakeho duvodu proste nefunguje ABSOLUTE_URL
                return $this->json(['token' => 'ABC', 'data' => ['redirect' => $this->generateUrl('bulkgate_main_app', [], UrlGeneratorInterface::ABSOLUTE_URL) . '#/dashboard']]);
            case "logout":
                return $this->json(['token' => 'EFG', 'data' => ['redirect' => $this->generateUrl('bulkgate_main_app')]]);
            case "authenticate":
                return $this->json($authenticate->run($this->generateUrl('bulkgate_main_app', [], UrlGeneratorInterface::ABSOLUTE_URL) . '#/sign/in'));
            case "module-settings":
                $data = Json::decode($request->getContent());

                return $this->json($settings_change->run($data));
        }
    }
}
