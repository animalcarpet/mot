<?php

$config = [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];

return $config;
