<?php

namespace IntegrationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaEntities\Entity\MotTest;
use IntegrationApi\DvlaVehicle\Service\DvlaVehicleUpdatedService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DvlaVehicleUpdatedServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $em */
        $em = $serviceLocator->get(EntityManager::class);

        return new DvlaVehicleUpdatedService(
            $em->getRepository(MotTest::class),
            $serviceLocator->get("ReplacementCertificateService"),
            $serviceLocator->get(VehicleService::class)
        );
    }
}
