<?php declare(strict_types=1);

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Symfony\Component\Finder\Finder;
use PrestaShop\CodingStandards\CsFixer\Config;

$config = new Config();

/**
 * @var Finder $finder
 */
$finder = $config->setUsingCache(true)->getFinder();
$finder->in(__DIR__)->exclude('vendor');

return $config;
