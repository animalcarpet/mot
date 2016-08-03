<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('config')
    ->exclude('test')
    ->exclude('vendor')
    ->in(__DIR__ . '/../../mot-common-web-module')
    ->in(__DIR__ . '/../../mot-web-frontend/module')
    ->in(__DIR__ . '/../../mot-web-frontend/src')
;

return new Sami($iterator, [
    'title'                => 'MOT Frontend PHP Documentation',
    'theme'                => 'gov-uk',
    'build_dir'            => __DIR__ . '/build',
    'cache_dir'            => __DIR__ . '/cache',
    'template_dirs'        => [__DIR__ . '/../themes/gov-uk'],
    'include_parent_data'  => false,
    'default_opened_level' => 0,
]);