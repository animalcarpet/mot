<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsConfirmationAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsConfirmationActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $changeSecurityQuestionsStepService = $serviceLocator->get(ChangeSecurityQuestionsStepService::class);

        /** @var ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService */
        $changeSecurityQuestionsSessionService = $serviceLocator->get(ChangeSecurityQuestionsSessionService::class);

        return new ChangeSecurityQuestionsConfirmationAction(
            $changeSecurityQuestionsStepService,
            $changeSecurityQuestionsSessionService
        );
    }
}