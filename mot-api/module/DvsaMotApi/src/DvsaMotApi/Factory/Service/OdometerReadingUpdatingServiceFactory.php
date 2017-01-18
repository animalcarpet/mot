<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Factory\Service;

use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaMotApi\Service\OdometerReadingUpdatingService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class OdometerReadingUpdatingServiceFactory
 */
class OdometerReadingUpdatingServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OdometerReadingUpdatingService(
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('MotTestSecurityService'),
            $serviceLocator->get('MotTestValidator'),
            $serviceLocator->get(ApiPerformMotTestAssertion::class)
        );
    }
}
