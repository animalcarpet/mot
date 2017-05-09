<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsConfirmationAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsConfirmationController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsConfirmationControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var ChangeSecurityQuestionsConfirmationAction $action $action */
        $action = $serviceLocator->get(ChangeSecurityQuestionsConfirmationAction::class);

        return new ChangeSecurityQuestionsConfirmationController($action);
    }
}
