<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Controller\EmailController;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use UserAdmin\Service\IsEmailDuplicateService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for EmailController instances.
 */
class EmailControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EmailController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $isEmailDuplicateService = $serviceLocator->get(IsEmailDuplicateService::class);

        $stepService = $serviceLocator->get(RegistrationStepService::class);

        return new EmailController($stepService, $isEmailDuplicateService);
    }
}
