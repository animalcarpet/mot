<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use NotificationApi\Service\NotificationService;
use SiteApi\Service\SiteService;
use PersonApi\Service\DashboardService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DashboardServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var VehicleService $vehicleService */
        $vehicleService = $serviceLocator->get(VehicleService::class);

        /** @var ParamObfuscator $paramObfuscator */
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);

        return new DashboardService(
            $entityManager,
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(SiteService::class),
            $serviceLocator->get(SpecialNoticeService::class),
            $serviceLocator->get(NotificationService::class),
            $serviceLocator->get(PersonalAuthorisationForMotTestingService::class),
            $serviceLocator->get('TesterService'),
            $entityManager->getRepository(AuthorisationForAuthorisedExaminer::class),
            $vehicleService,
            $paramObfuscator
        );
    }
}
