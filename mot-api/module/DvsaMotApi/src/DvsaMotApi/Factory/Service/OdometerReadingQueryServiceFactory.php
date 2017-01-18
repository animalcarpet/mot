<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

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
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(ReadMotTestAssertion::class),
            $serviceLocator->get(MotTestRepository::class),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
