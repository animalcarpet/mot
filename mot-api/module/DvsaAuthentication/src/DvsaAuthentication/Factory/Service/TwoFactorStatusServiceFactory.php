<?php

namespace DvsaAuthentication\Factory\Service;

use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaEntities\Repository\PersonRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TwoFactorStatusServiceFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get(AuthorisationService::class);

        /** @var PersonRepository $personRepository */
        $personRepository = $serviceLocator->get(PersonRepository::class);

        return new TwoFactorStatusService(
            $authorisationService,
            $personRepository
        );
    }
}
