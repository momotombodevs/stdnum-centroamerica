<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())->in([__DIR__ . '/src', __DIR__ . '/tests']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
