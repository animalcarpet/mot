<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\ForgotSecurityQuestionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ForgotSecurityQuestionControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LostOrForgottenCardController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $config = $serviceLocator->get('config');

        return new ForgotSecurityQuestionController(
            $config
        );
    }
}
