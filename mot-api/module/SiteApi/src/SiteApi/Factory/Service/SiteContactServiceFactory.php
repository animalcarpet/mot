<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use SiteApi\Service\SiteContactService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteServiceFactory.
 */
class SiteContactServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dvsaAuthService = $serviceLocator->get('DvsaAuthorisationService');

        return new SiteContactService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(ContactDetailsService::class),
            $serviceLocator->get(XssFilter::class),
            new UpdateVtsAssertion($dvsaAuthService)
        );
    }
}
