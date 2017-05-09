<?php

namespace DvsaMotApi\Factory\Service\Validator;

use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestChangeValidatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotTestStatusChangeValidator(
            $serviceLocator->get('MotTestStatusService')
        );
    }
}
