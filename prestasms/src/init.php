<?php
/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

define('_BG_PRESTASMS_DIR_', __DIR__.'/../../');

define('_BG_PRESTASMS_NAME_', 'PrestaSMS');
define('_BG_PRESTASMS_AUTHOR_', 'TOPefekt s.r.o.');
define('_BG_PRESTASMS_AUTHOR_URL_', 'https://www.bulkgate.com/');
define('_BG_PRESTASMS_PS_MIN_VERSION_', '1.6.0.0');
define('_BG_PRESTASMS_SLUG_', 'bg_prestasms');
define('_BG_PRESTASMS_VERSION_', '4.5.1');
define('_BG_PRESTASMS_DEMO_', false);

if(!file_exists(_BG_PRESTASMS_DIR_.'/extensions/src/_extension.php'))
{
    echo 'PrestaSMS: BulkGate extensions (https://github.com/BulkGate/extensions) must be installed.';
    exit;
}

require_once _BG_PRESTASMS_DIR_.'/extensions/src/_extension.php';
require_once __DIR__.'/_extension.php';

file_exists(_BG_PRESTASMS_DIR_.'/extensions/src/debug.php') && require_once _BG_PRESTASMS_DIR_.'/extensions/src/debug.php';

require_once _BG_PRESTASMS_DIR_.'/controllers/admin/PrestaSmsController.php';