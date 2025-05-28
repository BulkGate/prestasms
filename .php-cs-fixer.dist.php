<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

$finder = PhpCsFixer\Finder::create();
$finder
	->in(__DIR__)
	->exclude('vendor');

return $config
	->setUsingCache(true)
	->setFinder($finder);
