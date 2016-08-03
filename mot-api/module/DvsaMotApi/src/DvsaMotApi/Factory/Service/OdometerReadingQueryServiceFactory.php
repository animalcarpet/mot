<?php

namespace DvsaMotApi\Factory\Service;

use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\OdometerReadingQueryService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OdometerReadingQueryServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OdometerReadingQueryService(
            $serviceLocator->get('OdometerReadingDeltaAnomalyChecker'),
            $serviceLocator->get('OdometerReadingRepository'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(ReadMotTestAssertion::class),
            $serviceLocator->get(MotTestRepository::class),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
