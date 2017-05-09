<?php

namespace OrganisationApi\Factory\Service;

use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\RoleAvailability;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class NominateVerifierFactory.
 */
class NominateVerifierFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NominationVerifier(
            $serviceLocator->get(RoleAvailability::class)
        );
    }
}
