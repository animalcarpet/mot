<?php

use Dvsa\Mot\Frontend\Plugin\AjaxResponsePlugin;

return [
    'session_namespace_prefixes' => [
        'DvsaMotEnforcement\\Session\\',
    ],
    'session' => [
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],
    'controller_plugins' => [
        'invokables' => [
            'ajaxResponse' => AjaxResponsePlugin::class,
        ],
    ],
    'view_manager' => [

        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'module_layouts' => [
        'Dvsa\Mot\Frontend\AuthenticationModule' => 'application/layout',
    ],
];
