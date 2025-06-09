<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

// get git's ignored files and format for cs-fixer's finder usage
$ignored = [];
exec('git ls-files --others --ignored --exclude-standard | cut -d/ -f1 | sort -u', $ignored);

$finder = PhpCsFixer\Finder::create();
$finder
	->in(__DIR__)
	->exclude('vendor');

foreach ($ignored as $pathOrFile)
{
	$finder->exclude($pathOrFile);
}

return $config
	->setUsingCache(true)
	->setFinder($finder);
