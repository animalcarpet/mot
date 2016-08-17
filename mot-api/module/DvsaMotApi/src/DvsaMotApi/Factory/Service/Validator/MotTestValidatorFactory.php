<?php

namespace DvsaMotApi\Factory\Service\Validator;

use DvsaMotApi\Service\Validator\MotTestValidator;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestValidatorFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotTestValidator(
            $serviceLocator->get('CensorService'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get(SpecialNoticeService::class),
            $serviceLocator->get('Feature\FeatureToggles')
        );
    }
}
