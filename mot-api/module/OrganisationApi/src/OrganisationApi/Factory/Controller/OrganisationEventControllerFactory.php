<?php

namespace OrganisationApi\Factory\Controller;

use OrganisationApi\Controller\OrganisationEventController;
use OrganisationApi\Service\OrganisationEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrganisationEventControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrganisationEventController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new OrganisationEventController(
            $serviceLocator->get(OrganisationEventService::class)
        );
    }
}
