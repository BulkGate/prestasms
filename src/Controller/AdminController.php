<?php

namespace BulkGate\PrestaSms\Controller;

use BulkGate\PrestaSms\Database\ConnectionPrestashop;
use BulkGate\Plugin\Utils\Jwt;
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
    public function __construct(ConnectionPrestashop $connection)
    {
        
    }

    public function indexAction()
    {
        //todo: development phase only!!!
        $permanent_token = 'YvyXRPEXBMk8ZICFIOUBCxr8XXpmVtvStXseQRldm2xq1JV1ij';
        $token = Jwt::encode([
            'application_id' => 18,
            'application_installation' => 'https://shop.example.com',
            'application_product' => 'ps',
            'application_language' => 'cs',
            'application_version' => '5.0.10',
            'application_parameters' => [
                'guest' => false,
                //'expire' => time() + 10
            ]],
            $permanent_token
        );

        return $this->render('@Modules/bg_prestasms/views/index.html.twig', [
            'layoutTitle' => 'BulkGate SMS',
            'showContentHeader' => false,
            'token' => $token,
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

    public function proxyAction(string $action): JsonResponse
    {
        switch($action)
        {
            case "login":
                // https://github.com/PrestaShop/PrestaShop/issues/18703 - z nejakeho duvodu proste nefunguje ABSOLUTE_URL
                return $this->json(['token' => 'ABC', 'data' => ['redirect' => $this->generateUrl('bulkgate_main_app', [], UrlGeneratorInterface::ABSOLUTE_URL) . '#/dashboard']]);
            case "logout":
                return $this->json(['token' => 'EFG', 'data' => ['redirect' => $this->generateUrl('bulkgate_main_app')]]);
            case "authenticate":
                return $this->json(['token' => 'IJK', 'redirect' => $this->generateUrl('bulkgate_main_app')]);
            case "module-settings":
                return $this->json(['error' => 'not impl.']);
        }
    }
}