<?php

namespace UserAdmin\Factory\Service;

use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonRoleManagementServiceFactory implements FactoryInterface
{
    /**
     * Create PersonRoleManagementService service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return PersonRoleManagementService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get("AuthorisationService");

        /** @var HttpRestJsonClient $httpRestJsonClient */
        $httpRestJsonClient = $serviceLocator->get(HttpRestJsonClient::class);

        $catalogService = $serviceLocator->get('CatalogService');

        $service = new PersonRoleManagementService(
            $authorisationService,
            $httpRestJsonClient,
            $catalogService
        );

        return $service;
    }
}