<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $changeSecurityQuestionsStepService = $serviceLocator->get(ChangeSecurityQuestionsStepService::class);

        /** @var ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService */
        $changeSecurityQuestionsSessionService = $serviceLocator->get(ChangeSecurityQuestionsSessionService::class);

        $passwordValidationService = $serviceLocator->get(PasswordValidationService::class);

        return new ChangeSecurityQuestionsAction(
            $changeSecurityQuestionsStepService,
            $changeSecurityQuestionsSessionService,
            $passwordValidationService
        );
    }
}
