<?php

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Utility\Hydrator;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEventApi\Service\EventService;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use SiteApi\Factory\Model\NominationVerifierFactory;
use SiteApi\Factory\Service\EnforcementSiteAssessmentServiceFactory;
use SiteApi\Factory\Service\EnforcementSiteAssessmentValidatorFactory;
use SiteApi\Factory\Service\MotTestInProgressServiceFactory;
use SiteApi\Factory\Service\MotTestLogServiceFactory;
use SiteApi\Factory\Service\SiteContactServiceFactory;
use SiteApi\Factory\Service\SiteDetailsServiceFactory;
use SiteApi\Factory\Service\SiteEventServiceFactory;
use SiteApi\Factory\Service\SiteSearchServiceFactory;
use SiteApi\Factory\Service\SiteServiceFactory;
use SiteApi\Factory\Service\SiteSlotUsageServiceFactory;
use SiteApi\Factory\Service\SiteTestingFacilitiesServiceFactory;
use SiteApi\Factory\Service\TestersAnnualAssessmentServiceFactory;
use SiteApi\Model\NominationVerifier;
use SiteApi\Model\Operation\NominateOperation;
use SiteApi\Service\DefaultBrakeTestsService;
use SiteApi\Service\EnforcementSiteAssessmentService;
use SiteApi\Service\EquipmentService;
use SiteApi\Service\Mapper\SiteBusinessRoleMapper;
use SiteApi\Service\MotTestInProgressService;
use SiteApi\Service\MotTestLogService;
use SiteApi\Service\NominateRoleService;
use SiteApi\Service\SiteBusinessRoleService;
use SiteApi\Service\SiteContactService;
use SiteApi\Service\SiteDetailsService;
use SiteApi\Service\SiteEventService;
use SiteApi\Service\SiteNominationService;
use SiteApi\Service\SitePositionService;
use SiteApi\Service\SiteSearchService;
use SiteApi\Service\SiteService;
use SiteApi\Service\SiteSlotUsageService;
use SiteApi\Service\SiteTestingDailyScheduleService;
use SiteApi\Service\SiteTestingFacilitiesService;
use SiteApi\Service\TestersAnnualAssessmentService;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteTestingDailyScheduleValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

return [
    'factories' => [
        Hydrator::class => function (ServiceLocatorInterface $sm) {
            return new Hydrator();
        },
        SiteBusinessRoleService::class => function (ServiceLocatorInterface $sm) {
            return new SiteBusinessRoleService(
                    new SiteBusinessRoleMapper(),
                    $sm->get(\Doctrine\ORM\EntityManager::class)
                        ->getRepository(\DvsaEntities\Entity\SiteBusinessRole::class),
                    $sm->get(\Doctrine\ORM\EntityManager::class)
                        ->getRepository(\DvsaEntities\Entity\SiteBusinessRoleMap::class),
                    $sm->get('DvsaAuthorisationService')
                );
        },
        SitePositionService::class => function (ServiceManager $sm) {
            $entityManager = $sm->get(EntityManager::class);

            return new SitePositionService(
                    $sm->get(EventService::class),
                    $entityManager->getRepository(SiteBusinessRoleMap::class),
                    $sm->get('DvsaAuthorisationService'),
                    $entityManager,
                    $sm->get(NotificationService::class),
                    $sm->get(MotIdentityProviderInterface::class),
                    $sm->get(MotTestRepository::class),
                    $sm->get(UserOrganisationNotificationService::class)
                );
        },
        NominateRoleService::class => function (ServiceManager $sm) {
            $em = $sm->get(EntityManager::class);

            return new NominateRoleService(
                    $sm->get(\Doctrine\ORM\EntityManager::class),
                    $sm->get('DvsaAuthenticationService'),
                    $sm->get('DvsaAuthorisationService'),
                    $sm->get(NominateOperation::class),
                    new Transaction($em)
                );
        },
        EquipmentService::class => function (ServiceLocatorInterface $sm) {
            return new EquipmentService(
                    $sm->get(EntityManager::class)->getRepository(Site::class),
                    $sm->get('DvsaAuthorisationService')
                );
        },
        NominateOperation::class => function (ServiceLocatorInterface $sm) {
            return new NominateOperation(
                    $sm->get(EntityManager::class),
                    $sm->get(NominationVerifier::class),
                    $sm->get(SiteNominationService::class)
                );
        },
        NominationVerifier::class => NominationVerifierFactory::class,
        SiteNominationService::class => function (ServiceLocatorInterface $sm) {
            return new SiteNominationService(
                    $sm->get(NotificationService::class),
                    $sm->get(TwoFactorStatusService::class),
                    $sm->get('Feature\FeatureToggles')
                );
        },
        SiteTestingDailyScheduleService::class => function (ServiceLocatorInterface $sm) {
            return new SiteTestingDailyScheduleService(
                    $sm->get(EntityManager::class)->getRepository(
                        SiteTestingDailySchedule::class
                    ),
                    $sm->get(EntityManager::class)->getRepository(Site::class),
                    new SiteTestingDailyScheduleValidator(),
                    $sm->get('DvsaAuthorisationService')
                );
        },
        DefaultBrakeTestsService::class => function (ServiceLocatorInterface $sm) {
            return new DefaultBrakeTestsService(
                    $sm->get(EntityManager::class)->getRepository(Site::class),
                    $sm->get(EntityManager::class)->getRepository(BrakeTestType::class),
                    new BrakeTestConfigurationValidator(),
                    $sm->get('DvsaAuthorisationService')
                );
        },
        MotTestInProgressService::class => MotTestInProgressServiceFactory::class,
        SiteSlotUsageService::class => SiteSlotUsageServiceFactory::class,
        SiteContactService::class => SiteContactServiceFactory::class,
        SiteSearchService::class => SiteSearchServiceFactory::class,
        SiteService::class => SiteServiceFactory::class,
        SiteTestingFacilitiesService::class => SiteTestingFacilitiesServiceFactory::class,
        SiteDetailsService::class => SiteDetailsServiceFactory::class,
        SiteEventService::class => SiteEventServiceFactory::class,
        EnforcementSiteAssessmentService::class => EnforcementSiteAssessmentServiceFactory::class,
        EnforcementSiteAssessmentValidator::class => EnforcementSiteAssessmentValidatorFactory::class,
        MotTestLogService::class => MotTestLogServiceFactory::class,
        TestersAnnualAssessmentService::class => TestersAnnualAssessmentServiceFactory::class
    ],
    'invokables' => [
        TestingFacilitiesValidator::class => TestingFacilitiesValidator::class,
        SiteDetailsValidator::class => SiteDetailsValidator::class,
    ],
];
