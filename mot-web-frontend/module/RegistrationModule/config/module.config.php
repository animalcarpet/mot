<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
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
