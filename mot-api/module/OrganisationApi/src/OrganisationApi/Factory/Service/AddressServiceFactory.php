<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Service\AddressService;
use DvsaCommonApi\Service\Validator\AddressValidator;
use DvsaEntities\Mapper\AddressMapper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AddressServiceFactory.
 */
class AddressServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AddressService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(Hydrator::class),
            new AddressValidator(),
            new AddressMapper()
        );
    }
}
