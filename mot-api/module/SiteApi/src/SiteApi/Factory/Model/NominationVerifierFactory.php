<?php

namespace SiteApi\Factory\Model;

use SiteApi\Factory\SitePersonnelFactory;
use SiteApi\Model\NominationVerifier;
use SiteApi\Model\RoleRestriction\SiteAdminRestriction;
use SiteApi\Model\RoleRestriction\SiteManagerRestriction;
use SiteApi\Model\RoleRestriction\TesterRestriction;
use SiteApi\Model\RoleRestrictionsSet;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NominationVerifierFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return NominationVerifier
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authService = $serviceLocator->get('DvsaAuthorisationService');

        $model = new NominationVerifier(
            new RoleRestrictionsSet(
                [
                    new TesterRestriction($authService),
                    new SiteManagerRestriction($authService),
                    new SiteAdminRestriction($authService),
                ]
            ),
            new SitePersonnelFactory()
        );

        return $model;
    }
}
