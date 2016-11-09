<?php

namespace UserAdmin\Factory\Service;

use DvsaClient\MapperFactory;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for {@link \UserAdmin\Service\HelpdeskAccountAdminService}.
 */
class HelpdeskAccountAdminServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return HelpdeskAccountAdminService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get("AuthorisationService");
        /** @var MapperFactory $mapperFactory */
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new HelpdeskAccountAdminService(
            $authorisationService,
            $mapperFactory->UserAdmin
        );
    }
}
