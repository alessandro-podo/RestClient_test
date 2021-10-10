<?php

declare(strict_types=1);
//.php_cs.dist
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('var')
    ->exclude('config')
    ->exclude('build')
    ->exclude('migrations')
    ->notPath('src/Kernel.php')
    ->notPath('public/index.php')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'mb_str_functions' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'RemoveDebugStatements/dump' => true,
        'single_line_comment_style' => false,
        'phpdoc_to_comment' => false,
        'strict_comparison' => false, #weil es auch an den Stellen auf === stellt, wo es falsch ist
    ])
    ->registerCustomFixers([new Drew\DebugStatementsFixers\Dump()])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
