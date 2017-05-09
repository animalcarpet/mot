<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Core\Form\View\Helper\MotFormLabel;
use Core\Form\View\Helper\MotFormRow;
use Core\View\Helper\Factory\GetReleaseTagFactory;
use DvsaFeature\Factory\View\Helper\FeatureToggleViewHelperFactory;

return [
    'view_manager' => [
        'template_map' => [
            'googleAnalyticsSnippet' => __DIR__.'/../view/partial/fragments/google-analytics.phtml',
            'layout/layout' => __DIR__.'/../view/layout/layout.phtml',
            'contentBreadcrumb' => __DIR__.'/../view/partial/fragments/content-breadcrumb.twig',
        ],
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'getReleaseTag' => GetReleaseTagFactory::class,
            'googleAnalyticsHelper' => \Core\Factory\GoogleAnalyticsHelperFactory::class,
            'featureToggle' => FeatureToggleViewHelperFactory::class,
        ],
        'invokables' => [
            'MotFormRow' => MotFormRow::class,
            'MotFormLabel' => MotFormLabel::class,
        ],
    ],
];
