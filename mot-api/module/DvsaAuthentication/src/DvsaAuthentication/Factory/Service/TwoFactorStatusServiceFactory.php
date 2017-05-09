<?php

namespace DvsaAuthentication\Factory\Service;

use Dvsa\Mot\ApiClient\Service\AuthorisationService as AuthorisationServiceClient;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaEntities\Repository\PersonRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TwoFactorStatusServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthorisationServiceClient $authorisationServiceClient */
        $authorisationServiceClient = $serviceLocator->get(AuthorisationServiceClient::class);

        /** @var AuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');

        /** @var PersonRepository $personRepository */
        $personRepository = $serviceLocator->get(PersonRepository::class);

        return new TwoFactorStatusService(
            $authorisationServiceClient,
            $authorisationService,
            $personRepository
        );
    }
}
