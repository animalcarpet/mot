<?php

namespace Application\Factory\Data;

use Application\Data\ApiUserSiteCount;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class ApiUserSiteCountFactory.
 */
class ApiUserSiteCountFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ApiUserSiteCount
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiUserSiteCount($serviceLocator->get(HttpRestJsonClient::class));
    }
}
