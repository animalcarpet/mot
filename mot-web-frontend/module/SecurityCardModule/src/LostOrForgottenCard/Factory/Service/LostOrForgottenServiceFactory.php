<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use DvsaClient\Mapper\UserAdminMapper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class LostOrForgottenServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LostOrForgottenServiceFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var HttpRestJsonClient $httpRestJsonClient */
        $httpRestJsonClient = $serviceLocator->get(HttpRestJsonClient::class);
        $userAdminMapper = new UserAdminMapper($httpRestJsonClient);

        $lostOrForgottenSessionService = $serviceLocator->get(LostOrForgottenSessionService::class);

        return new LostOrForgottenService(
            $userAdminMapper,
            $lostOrForgottenSessionService
        );
    }
}
