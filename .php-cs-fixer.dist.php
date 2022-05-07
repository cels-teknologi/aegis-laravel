<?php

$finder = PhpCsFixer\Finder::create()
                           ->exclude('vendor')
                           ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR12' => true,
    'elseif' => true,
    'array_indentation' => true,
    'control_structure_continuation_position' => ['position' => 'next_line'],
    'new_with_braces' => false,
    'no_superfluous_elseif' => true,
])
->setFinder($finder);