<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionTwoAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionTwoActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $changeSecurityQuestionsService = $serviceLocator->get(ChangeSecurityQuestionsService::class);

        $changeSecurityQuestionsStepService = $serviceLocator->get(ChangeSecurityQuestionsStepService::class);

        return new ChangeSecurityQuestionTwoAction(
            $changeSecurityQuestionsService,
            $changeSecurityQuestionsStepService
        );
    }
}
