<?php

declare(strict_types=1);

$rules = [

    // Section: sets
    '@PER-CS3x0' => true,
    '@PHP81Migration' => true,

    // Section: rules
    'array_push' => true,
    'mb_str_functions' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'whitespace_after_comma_in_array' => true,
    'modernize_types_casting' => true,
    'explicit_string_variable' => false,
    'ordered_types' => [
        'case_sensitive' => true,
        'sort_algorithm' => 'none',
        'null_adjustment' => 'always_last',
    ],
    'no_alternative_syntax' => true,
    'native_function_invocation' => [
        'include' => ['@internal'],
        'scope' => 'all',
        'strict' => true,
    ],
    'global_namespace_import' => [
        'import_classes' => false,
        'import_constants' => false,
        'import_functions' => true,
    ],
    'ordered_imports'  => [
        'case_sensitive' => true,
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
    ],
    'no_unused_imports' => true,
    'no_homoglyph_names' => true,
    'assign_null_coalescing_to_coalesce_equal' => true,
    'ternary_to_null_coalescing' => true,
    'unary_operator_spaces' => true,
    'long_to_shorthand_operator' => true,
    'strict_comparison' => true,
    'braces_position' => [
        'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
    ],
    'declare_strict_types' => true,
    'strict_param' => true,
    'yoda_style' => true,
    'final_class' => true,

    // Section: docs
    'no_empty_comment' => true,
    'no_empty_phpdoc' => true,
    'phpdoc_line_span' => [
        'const' => 'single',
        'method' => 'single',
        'property' => 'single',
    ],
    'no_superfluous_phpdoc_tags' => true,
];


$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
;
