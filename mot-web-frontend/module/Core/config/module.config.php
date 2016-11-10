<?php

use Core\Factory\Helper\AssetHelperFactory;
use Core\View\Helper\Factory\GetReleaseTagFactory;
use DvsaFeature\Factory\View\Helper\FeatureToggleViewHelperFactory;

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
            'motAsset' => AssetHelperFactory::class,
            'getReleaseTag' => GetReleaseTagFactory::class,
            'googleAnalyticsHelper' => \Core\Factory\GoogleAnalyticsHelperFactory::class,
            'featureToggle' => FeatureToggleViewHelperFactory::class
        ]
    ]
];