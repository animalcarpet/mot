<?php

namespace NotificationApi\Factory\Service;

use DvsaAuthorisation\Service\AuthorisationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PositionRemovalNotificationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');

        $roles = $authorisationService->getAuthorizationDataAsArray();

        if (empty($roles)) {
            throw new \InvalidArgumentException('Roles are not valid');
        }

        if (
            !array_key_exists('sites', $roles) ||
            !array_key_exists('organisations', $roles) ||
            !array_key_exists('normal', $roles
            )
        ) {
            throw new \InvalidArgumentException('Site/Organisation/System roles must be defined');
        }

        return new PositionRemovalNotificationService($roles);
    }
}
