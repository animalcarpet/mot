<?php

namespace NotificationApi\Factory\Service\Helper;

use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaFeature\FeatureToggles;
use NotificationApi\Service\Helper\NotificationTradeTemplateHelper;
use Zend\ServiceManager\FactoryInterface;
use DvsaEntities\Repository\PersonRepository;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotificationTradeTemplateHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');
        $identityProvider = $serviceLocator->get('MotIdentityProvider');
        $personRepository = $serviceLocator->get(PersonRepository::class);
        $authService = $serviceLocator->get(AuthorisationService::class);

        return new NotificationTradeTemplateHelper(
            $authService,
            $identityProvider,
            $personRepository,
            $featureToggles
        );
    }
}
