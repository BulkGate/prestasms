<?php

require_once __DIR__.'/../../prestasms/src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsBlackListImportController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('black_list', 'Black list');
    }

    public function renderView()
    {
        return $this->prestaSmsView("BlackList", "import", true);
    }
}
