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
    '@zxing/browser' => [
        'version' => '0.1.4',
    ],
    '@zxing/library' => [
        'version' => '0.20.0',
    ],
    'ts-custom-error' => [
        'version' => '3.3.1',
    ],
    'flowbite' => [
        'version' => '2.3.0',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'flowbite/dist/flowbite.min.css' => [
        'version' => '2.3.0',
        'type' => 'css',
    ],
    'flowbite-datepicker' => [
        'version' => '1.2.6',
    ],
    'flowbite-datepicker/Datepicker' => [
        'version' => '1.2.6',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
];
