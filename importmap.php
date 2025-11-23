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
        'version' => '1.12.26',
    ],
    'image-validator' => [
        'version' => '1.2.1',
    ],
    'papaparse' => [
        'version' => '5.5.3',
    ],
    'pdfjs-dist' => [
        'version' => '5.4.394',
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
        'version' => '11.26.3',
    ],
    'admin-lte' => [
        'version' => '4.0.0-rc3',
    ],
    'admin-lte/dist/css/adminlte.min.css' => [
        'version' => '4.0.0-rc3',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'icheck' => [
        'version' => '1.0.2',
    ],
    'jquery-form' => [
        'version' => '4.3.0',
    ],
    'jquery-slimscroll' => [
        'version' => '1.3.8',
    ],
    'jquery-ui' => [
        'version' => '1.14.1',
    ],
    'jquery.scrollto' => [
        'version' => '2.1.3',
    ],
    'masonry-layout' => [
        'version' => '4.2.2',
    ],
    'select2' => [
        'version' => '4.1.0-rc.0',
    ],
    'outlayer' => [
        'version' => '2.1.1',
    ],
    'get-size' => [
        'version' => '2.0.3',
    ],
    'select2/dist/css/select2.min.css' => [
        'version' => '4.1.0-rc.0',
        'type' => 'css',
    ],
    'ev-emitter' => [
        'version' => '1.1.0',
    ],
    'fizzy-ui-utils' => [
        'version' => '2.0.5',
    ],
    'desandro-matches-selector' => [
        'version' => '2.0.2',
    ],
    'qs' => [
        'version' => '6.14.0',
    ],
    'side-channel' => [
        'version' => '1.1.0',
    ],
    'es-errors/type' => [
        'version' => '1.3.0',
    ],
    'object-inspect' => [
        'version' => '1.13.3',
    ],
    'side-channel-list' => [
        'version' => '1.0.0',
    ],
    'side-channel-map' => [
        'version' => '1.0.1',
    ],
    'side-channel-weakmap' => [
        'version' => '1.0.2',
    ],
    'get-intrinsic' => [
        'version' => '1.2.5',
    ],
    'call-bound' => [
        'version' => '1.0.2',
    ],
    'es-errors' => [
        'version' => '1.3.0',
    ],
    'es-errors/eval' => [
        'version' => '1.3.0',
    ],
    'es-errors/range' => [
        'version' => '1.3.0',
    ],
    'es-errors/ref' => [
        'version' => '1.3.0',
    ],
    'es-errors/syntax' => [
        'version' => '1.3.0',
    ],
    'es-errors/uri' => [
        'version' => '1.3.0',
    ],
    'gopd' => [
        'version' => '1.2.0',
    ],
    'es-define-property' => [
        'version' => '1.0.1',
    ],
    'has-symbols' => [
        'version' => '1.1.0',
    ],
    'dunder-proto/get' => [
        'version' => '1.0.0',
    ],
    'call-bind-apply-helpers/functionApply' => [
        'version' => '1.0.0',
    ],
    'call-bind-apply-helpers/functionCall' => [
        'version' => '1.0.0',
    ],
    'function-bind' => [
        'version' => '1.1.2',
    ],
    'hasown' => [
        'version' => '2.0.2',
    ],
    'call-bind' => [
        'version' => '1.0.8',
    ],
    'call-bind-apply-helpers' => [
        'version' => '1.0.0',
    ],
    'set-function-length' => [
        'version' => '1.2.2',
    ],
    'call-bind-apply-helpers/applyBind' => [
        'version' => '1.0.0',
    ],
    'define-data-property' => [
        'version' => '1.1.4',
    ],
    'has-property-descriptors' => [
        'version' => '1.0.2',
    ],
    '@wlindabla/form_validator' => [
        'version' => '2.3.3',
    ],
];
