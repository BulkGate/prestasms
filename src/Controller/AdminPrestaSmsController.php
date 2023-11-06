<?php

namespace BulkGate\PrestaSms\Controller;

use BulkGate\Plugin\DI\Container;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminController extends FrameworkBundleAdminController
{
    private Container $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public function indexAction(Request $request)
    {
        dump($this->di);

        return $this->render('@Modules/bg_prestasms/views/index.html.twig', [
            'layoutTitle' => 'BulkGate SMS',
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
}
