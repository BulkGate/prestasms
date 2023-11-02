<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsSignInController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('sign_in', 'Sign in');
    }
    
    public function renderView()
    {
        $this->ps_proxy->add('login', 'login');
        return $this->prestaSmsView("ModuleSign", "in");
    }

    public function ajaxProcessLogin()
    {
        $response =  $this->ps_di->getProxy()->login(array_merge(array("name" => Configuration::get('PS_SHOP_NAME')), Tools::getValue('__bulkgate')));

        if($response instanceof Extensions\IO\Response)
        {
            Extensions\JsonResponse::send($response);
        }
        Extensions\JsonResponse::send(array('token' => $response, 'redirect' => $this->context->link->getAdminLink('AdminPrestaSmsDashboardDefault')));
    }
}
