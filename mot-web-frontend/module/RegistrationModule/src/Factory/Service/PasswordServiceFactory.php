<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Service\PasswordService;
use DvsaCommon\Validator\PasswordValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PasswordServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PasswordController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PasswordService(new PasswordValidator());
    }
}
