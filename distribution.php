<?php

declare(strict_types=1);

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */

/**
 * WhiteLabel re-branding
 */
const BulkGateWhiteLabel = 'BulkGate';

const BulkGateWhiteLabelUrl = 'https://portal.bulkgate.com';

const BulkGateWhiteLabelModuleWebsite = 'https://www.bulkgate.com/en/integrations/prestasms-sms-module-for-prestashop/';

/**
 * Internals
 */
const BulkGateModuleVersion = '6.0.0';

const BulkGateMinimalPrestashopVersion = '1.7.8.0';

const BulkGateApiVersion = '1.0';

/**
 * Affiliate program
 */
if (file_exists(__DIR__ . '/affiliate.php')) {
    require_once __DIR__ . '/affiliate.php';
}

if (!defined('BulkGateAffiliateId')) {
    define('BulkGateAffiliateId', null);
}
