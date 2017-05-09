<?php

namespace Application\View\HelperFactory;

use Application\Data\ApiUserSiteCount;
use Application\View\Helper\GetSiteCount;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Exception\UnauthorisedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GetSiteCountFactory.
 */
class GetSiteCountFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $viewHelperServiceLocator
     *
     * @return GetSiteCount
     *
     * @throws UnauthorisedException
     */
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();
        /** @var MotIdentityProviderInterface $container */
        $identityProvider = $sl->get('MotIdentityProvider');
        $identity = $identityProvider->getIdentity();
        if (!$identity) {
            throw new UnauthorisedException('No identity provided');
        }
        /** @var ApiUserSiteCount $apiService */
        $apiService = $sl->get(ApiUserSiteCount::class);
        $helper = new GetSiteCount($apiService, $identity);

        return $helper;
    }
}
