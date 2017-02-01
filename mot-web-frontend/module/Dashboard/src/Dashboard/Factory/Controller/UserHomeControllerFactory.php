<?php

namespace Dashboard\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Controller\UserHomeController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\PersonStore;
use Dashboard\Service\TradeRolesAssociationsService;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;

class UserHomeControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new UserHomeController(
            $serviceLocator->get('LoggedInUserManager'),
            $serviceLocator->get(ApiPersonalDetails::class),
            $serviceLocator->get(PersonStore::class),
            $serviceLocator->get(ApiDashboardResource::class),
            $serviceLocator->get('CatalogService'),
            $serviceLocator->get(WebAcknowledgeSpecialNoticeAssertion::class),
            $serviceLocator->get(UserAdminSessionManager::class),
            $serviceLocator->get(TesterGroupAuthorisationMapper::class),
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(UserAdminSessionManager::class),
            $serviceLocator->get(ViewTradeRolesAssertion::class),
            $serviceLocator->get(TradeRolesAssociationsService::class)
        );
    }
}
