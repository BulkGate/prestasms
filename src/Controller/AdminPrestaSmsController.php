<?php

namespace BulkGate\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsController extends FrameworkBundleAdminController
{
    public function __construct()
    {

    }

    public function indexAction()
    {
        return $this->render('@Modules/bg_prestasms/templates/demo.html.twig');
    }

    public function demoAction(Request $request)
    {
        dump($this, $request);
        return $this->render('@Modules/bg_prestasms/templates/demo.html.twig', [
            'layoutTitle' => 'BulkGate SMS'
        ]);
    }
}
