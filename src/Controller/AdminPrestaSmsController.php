<?php

namespace BulkGate\Controller;
require_once __DIR__ . '/../../src/init.php';


use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsController extends FrameworkBundleAdminController
{
    public function __construct()
    {

    }

    public function demoAction()
    {
        return $this->render('@Modules/bg_prestasms/templates/demo.html.twig');
    }
}
