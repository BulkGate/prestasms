<?php

/** @see https://devdocs.prestashop-project.org/8/modules/concepts/controllers/front-controllers/#using-a-front-controller-as-a-cron-task */

$_GET['fc'] = 'module';
$_GET['module'] = 'bg_prestasms';
$_GET['controller'] = 'Cron';

require_once __DIR__ . '/../../index.php'; //require