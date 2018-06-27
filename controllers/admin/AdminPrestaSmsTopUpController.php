<?php

require_once __DIR__.'/../../prestasms/src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsTopUpController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('top_up', 'Top up');
    }

    public function renderView()
    {
        if(_BG_PRESTASMS_DEMO_)
        {
            \Tools::redirectAdmin($this->context->link->getAdminLink('AdminPrestaSmsDashboardDefault'));
        }
        return $this->prestaSmsView("Top", "up", true);
    }
}
