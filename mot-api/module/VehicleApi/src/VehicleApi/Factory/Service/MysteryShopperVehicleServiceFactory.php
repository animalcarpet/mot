<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Factory\Service;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\SiteRepository;
use Doctrine\ORM\EntityManager;
use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\Service\MysteryShopperVehicleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Repository\IncognitoVehicleRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Entity\Person;

/**
 * Class MysteryShopperVehicleServiceFactory.
 */
class MysteryShopperVehicleServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MysteryShopperVehicleService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthorisationService $authService */
        $authService = $serviceLocator->get('DvsaAuthorisationService');

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get(MotIdentityProviderInterface::class);

        /** @var MysteryShopperInputFilter $campaignDateValidator */
        $mysteryShopperInputFilter = new MysteryShopperInputFilter();

        /** @var EntityManager $em */
        $em = $serviceLocator->get(EntityManager::class);

        /** @var SiteRepository $siteRepository */
        $siteRepository = $em->getRepository(Site::class);

        /** @var IncognitoVehicleRepository $incognitoVehicleRepository */
        $incognitoVehicleRepository = $em->getRepository(IncognitoVehicle::class);

        /** @var VehicleRepository $VehicleRepository */
        $vehicleRepository = $em->getRepository(Vehicle::class);

        /** @var PersonRepository $personRepository */
        $personRepository = $em->getRepository(Person::class);

        /** @var MysteryShopperVehicleService $service */
        $service = new MysteryShopperVehicleService(
            $authService,
            $identityProvider,
            $mysteryShopperInputFilter,
            $siteRepository,
            $incognitoVehicleRepository,
            $vehicleRepository,
            $personRepository
        );

        return $service;
    }
}
