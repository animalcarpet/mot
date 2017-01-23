<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\OtpService;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use VehicleApi\Service\VehicleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotApi\Service\CreateMotTestService;
use OrganisationApi\Service\OrganisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;

class CreateMotTestServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new CreateMotTestService(
            $entityManager,
            $serviceLocator->get('MotTestValidator'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('TesterService'),
            $serviceLocator->get(RetestEligibilityValidator::class),
            $serviceLocator->get(OrganisationService::class),
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get(NewVehicleService::class),
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(MotTest::class),
            $serviceLocator->get(MysteryShopperHelper::class)
        );
    }
}
