<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var ChangeSecurityQuestionsAction $action $action */
        $action = $serviceLocator->get(ChangeSecurityQuestionsAction::class);

        /** @var ChangeSecurityQuestionsSessionService $sessionService $sessionService */
        $sessionService = $serviceLocator->get(ChangeSecurityQuestionsSessionService::class);

        return new ChangeSecurityQuestionsController(
            $action,
            $sessionService
        );
    }
}
