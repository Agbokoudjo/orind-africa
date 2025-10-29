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
    'admin' => [
        'path' => './assets/admin.js',
        'entrypoint' => true,
    ],
    'login' => [
        'path' => './assets/login.js',
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
    'libphonenumber-js' => [
        'version' => '1.12.24',
    ],
    'image-validator' => [
        'version' => '1.2.1',
    ],
    'papaparse' => [
        'version' => '5.5.3',
    ],
    'pdfjs-dist' => [
        'version' => '5.4.296',
    ],
    'xlsx' => [
        'version' => '0.18.5',
    ],
    'animate.css' => [
        'version' => '4.1.1',
    ],
    'animate.css/animate.min.css' => [
        'version' => '4.1.1',
        'type' => 'css',
    ],
    'sweetalert2' => [
        'version' => '11.26.2',
    ],
    '@wlindabla/form_validator' => [
        'version' => '2.1.1',
    ],
];
