<?php

namespace AccountApi\Factory\Controller;

use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Service\SecurityQuestionService;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class SecurityQuestionControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        $securityQuestionService = $serviceLocator->get(SecurityQuestionService::class);
        $personSecurityAnswerRecorder = $serviceLocator->get(PersonSecurityAnswerRecorder::class);

        return new SecurityQuestionController($securityQuestionService, $personSecurityAnswerRecorder);
    }
}
