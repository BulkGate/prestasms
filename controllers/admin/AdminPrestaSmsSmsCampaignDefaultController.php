<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsSmsCampaignDefaultController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('sms_campaigns', 'Sms Campaigns');
    }

    public function renderView()
    {
        return $this->prestaSmsView("SmsCampaign", "default", true);
    }
}
