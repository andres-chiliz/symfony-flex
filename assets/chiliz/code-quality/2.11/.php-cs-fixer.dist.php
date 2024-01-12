<?php

declare(strict_types=1);

$classesToSkip = [
    '/Rector\/[a-zA-Z]*\/src\/[a-zA-Z]*\.php/',
];
if (file_exists('phpcs-local-config.php')) {
    include_once 'phpcs-local-config.php';
    $classesToSkip = [...getClassesToSkip(), ...$classesToSkip];
}
// https://cs.symfony.com/doc/rules/index.html

$finder = PhpCsFixer\Finder::create()
    ->in([
        'src',
        'tests',
    ])->notPath($classesToSkip)
;

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'phpdoc_summary' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_order' => true,
        'phpdoc_separation' => [
            'groups' => [
                ['deprecated', 'link', 'see', 'since'],
                ['author', 'copyright', 'license'],
                ['category', 'package', 'subpackage'],
                ['property', 'property-read', 'property-write'],
                ['var'],
                ['param'],
                ['return'],
                ['throws'],
                ['ORM\\*'],
                ['Assert\\*'],
                ['Serializer\\*'],
                ['Security'],
                ['SuppressWarnings'],
                ['OA\\*'],
                ['FOSRest\\*'],
                ['ParamConverter\\*'],
                ['QueryParam\\*'],
                ['RouteParam\\*'],
                ['Rest\\*'],
                ['Permission'],
            ],
            'skip_unlisted_annotations' => true,
        ],
        'psr_autoloading' => false,
        'single_line_throw' => false,
        'simplified_null_return' => false,
        'yoda_style' => true,
        'blank_line_after_opening_tag' => false,
        'phpdoc_to_comment' => false,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'operator_linebreak' => ['position' => 'beginning', 'only_booleans' => true],
        'types_spaces' => ['space' => 'single'],
        'class_definition' => [
            'single_line' => true,
            'space_before_parenthesis' => true,
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
    ])
;

