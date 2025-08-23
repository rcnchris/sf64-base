<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'bootstrap' => [
        'version' => '5.3.7',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.7',
        'type' => 'css',
    ],
    'highlight.js/lib/core' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/apache' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/bash' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/css' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/javascript' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/json' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/makefile' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/php' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/sql' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/twig' => [
        'version' => '11.11.1',
    ],
    'highlight.js/lib/languages/yaml' => [
        'version' => '11.11.1',
    ],
    'highlight.js/styles/agate.css' => [
        'version' => '11.11.1',
        'type' => 'css',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'pivottable' => [
        'version' => '2.23.0',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'pivottable/dist/pivot.min.css' => [
        'version' => '2.23.0',
        'type' => 'css',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
    'datatables.net' => [
        'version' => '2.3.2',
    ],
    'datatables.net-bs5' => [
        'version' => '2.3.2',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '2.3.2',
        'type' => 'css',
    ],
    'datatables.net-responsive-bs5' => [
        'version' => '3.0.5',
    ],
    'datatables.net-responsive' => [
        'version' => '3.0.5',
    ],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css' => [
        'version' => '3.0.5',
        'type' => 'css',
    ],
    'inputmask' => [
        'version' => '5.0.9',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'moment' => [
        'version' => '2.30.1',
    ],
    'daterangepicker/daterangepicker.css' => [
        'version' => '3.1.0',
        'type' => 'css',
    ],
    'daterangepicker/daterangepicker.js' => [
        'version' => '3.1.0',
    ],
];
