<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\SurveyServiceFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Validation\ContingencyTestValidatorFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsContentBreadcrumbsBuilderFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use Dvsa\Mot\Frontend\MotTestModule\Validation\ContingencyTestValidator;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;

return [
    'factories' => [
        ContingencyTestValidator::class => ContingencyTestValidatorFactory::class,
        DefectsContentBreadcrumbsBuilder::class => DefectsContentBreadcrumbsBuilderFactory::class,
        SurveyService::class => SurveyServiceFactory::class,
    ],
];