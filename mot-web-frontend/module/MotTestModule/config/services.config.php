<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */
use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\RfrCacheFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\SurveyServiceFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Validation\ContingencyTestValidatorFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyContextProviderFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsContentBreadcrumbsBuilderFactory;
use Dvsa\Mot\Frontend\MotTestModule\Listener\SatisfactionSurveyListener;
use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCache;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use Dvsa\Mot\Frontend\MotTestModule\Validation\ContingencyTestValidator;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\Factory\View\DefectsJourneyUrlGeneratorFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Listener\SatisfactionSurveyListenerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SearchReasonForRejectionService;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\SearchReasonForRejectionServiceFactory;

return [
    'factories' => [
        ContingencyTestValidator::class => ContingencyTestValidatorFactory::class,
        DefectsContentBreadcrumbsBuilder::class => DefectsContentBreadcrumbsBuilderFactory::class,
        SurveyService::class => SurveyServiceFactory::class,
        DefectsJourneyContextProvider::class => DefectsJourneyContextProviderFactory::class,
        DefectsJourneyUrlGenerator::class => DefectsJourneyUrlGeneratorFactory::class,
        SatisfactionSurveyListener::class => SatisfactionSurveyListenerFactory::class,
        RfrCache::class => RfrCacheFactory::class,
        SearchReasonForRejectionService::class => SearchReasonForRejectionServiceFactory::class,
    ],
];
