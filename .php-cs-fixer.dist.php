<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php');

return (new Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(true)
;
