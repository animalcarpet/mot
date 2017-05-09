<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\IncognitoVehicleRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\VehicleRepository;
use VehicleApi\Factory\Service\MysteryShopperVehicleServiceFactory;
use VehicleApi\Service\MysteryShopperVehicleService;
use Zend\ServiceManager\ServiceManager;

class MysteryShopperVehicleServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        /** @var EntityManager $mockEntityManager */
        $mockEntityManager = XMock::of(EntityManager::class);
        $mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(
                function ($entity) {
                    switch ($entity) {
                        case Site::class:
                            return XMock::of(SiteRepository::class);
                            break;
                        case IncognitoVehicle::class:
                            return XMock::of(IncognitoVehicleRepository::class);
                            break;
                        case Vehicle::class:
                            return XMock::of(VehicleRepository::class);
                            break;
                        case Person::class:
                            return XMock::of(PersonRepository::class);
                            break;
                    }

                    throw new \Exception(sprintf('Im not capable to mock a repository for "%s" entity', $entity));
                }
            );

        /** @var ServiceManager $mockServiceManager */
        $mockServiceManager = new ServiceManager();
        $mockServiceManager->setService(
            EntityManager::class,
            $mockEntityManager
        )->setService(
            'DvsaAuthorisationService',
            XMock::of(AuthorisationServiceInterface::class)
        )->setService(
            MotIdentityProviderInterface::class,
            XMock::of(MotIdentityProviderInterface::class)
        );

        /** @var MysteryShopperVehicleServiceFactory $factory */
        $factory = new MysteryShopperVehicleServiceFactory();

        $this->assertInstanceOf(
            MysteryShopperVehicleService::class,
            $factory->createService($mockServiceManager)
        );
    }
}
