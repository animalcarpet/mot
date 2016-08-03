<?php

use Core\View\Helper\Factory\GetReleaseTagFactory;

return [
    'view_manager' => [
        'template_map'        => [
            'googleAnalyticsSnippet' => __DIR__ . '/../view/partial/fragments/google-analytics.phtml',
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'contentBreadcrumb' => __DIR__ . '/../view/partial/fragments/content-breadcrumb.twig',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'getReleaseTag' => GetReleaseTagFactory::class,
            'googleAnalyticsHelper' => \Core\Factory\GoogleAnalyticsHelperFactory::class
        ]
    ]
];