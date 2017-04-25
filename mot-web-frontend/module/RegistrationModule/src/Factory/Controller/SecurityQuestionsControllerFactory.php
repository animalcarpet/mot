<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Controller\SecurityQuestionsController;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for SecurityQuestionsController instances.
 */
class SecurityQuestionsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SecurityQuestionsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $stepService = $serviceLocator->get(RegistrationStepService::class);

        return new SecurityQuestionsController($stepService);
    }
}
