<?php

require_once __DIR__.'/../../prestasms/src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsModuleSettingsDefaultController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('settings', 'Settings');
    }

    public function renderView()
    {
        if(_BG_PRESTASMS_DEMO_)
        {
            \Tools::redirectAdmin($this->context->link->getAdminLink('AdminPrestaSmsDashboardDefault'));
        }
        $this->ps_proxy->add('save', 'save');
        $this->ps_proxy->add('logout', 'logout');
        return $this->prestaSmsView("ModuleSettings", "default");
    }

    public function ajaxProcessSave()
    {
        if(Tools::getValue('__bulkgate', false))
        {
            $this->ps_di->getProxy()->saveSettings(Tools::getValue('__bulkgate'));
        }
        Extensions\JsonResponse::send(array('redirect' => $this->context->link->getAdminLink($this->controller_name)));
    }


    public function ajaxProcessLogout()
    {
        $this->ps_di->getProxy()->logout();

        Extensions\JsonResponse::send(array('token' => 'guest', 'redirect' => $this->context->link->getAdminLink('AdminPrestaSmsSignIn')));
    }
}
