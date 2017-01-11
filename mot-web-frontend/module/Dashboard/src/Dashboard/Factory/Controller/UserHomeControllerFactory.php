<?php

namespace Dashboard\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Account\Service\SecurityQuestionService;
use Dashboard\Controller\UserHomeController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\PersonStore;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;

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
            $serviceLocator->get(SecurityQuestionService::class),
            $serviceLocator->get(UserAdminSessionManager::class)
        );
    }
}
