<?php

require_once __DIR__.'/../../prestasms/src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsVerifiedPhoneNumbersDefaultController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('verified_numbers', 'Verified numbers');
    }

    public function renderView()
    {
        return $this->prestaSmsView("VerifiedPhoneNumbers", "default", true);
    }
}
