<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionTwoAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionTwoControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $action = $serviceLocator->get(ChangeSecurityQuestionTwoAction::class);

        return new ChangeSecurityQuestionTwoController($action);
    }
}
