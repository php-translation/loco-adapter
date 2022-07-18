<?php

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
