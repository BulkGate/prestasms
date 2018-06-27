<?php

require_once __DIR__.'/../../prestasms/src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsModuleNotificationsCustomerController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('customer_sms', 'Customer SMS');
    }

    public function renderView()
    {
        $this->ps_proxy->add('save', 'save');
        return $this->prestaSmsView("ModuleNotifications", "customer", true);
    }

    public function ajaxProcessSave()
    {
        Extensions\JsonResponse::send(
            $this->ps_di->getProxy()->saveCustomerNotifications(Tools::getValue('__bulkgate'))
        );
    }
}
