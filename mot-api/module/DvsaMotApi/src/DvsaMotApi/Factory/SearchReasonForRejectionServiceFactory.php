<?php

namespace DvsaMotApi\Factory;

use DvsaEntities\Repository\RfrRepository;
use DvsaMotApi\Service\ReasonForRejection\SearchReasonForRejectionService;
use DvsaAuthorisation\Service\AuthorisationService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SearchReasonForRejectionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RfrRepository $rfrRepository */
        $rfrRepository = $serviceLocator->get("RfrRepository");

        /** @var AuthorisationService $dvsaAuthorisation */
        $dvsaAuthorisation = $serviceLocator->get("DvsaAuthorisationService");

        return new SearchReasonForRejectionService($dvsaAuthorisation, $rfrRepository);
    }
}
