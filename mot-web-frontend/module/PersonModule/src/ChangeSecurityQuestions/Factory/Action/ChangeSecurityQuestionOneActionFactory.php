<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionOneAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionOneActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $changeSecurityQuestionsService = $serviceLocator->get(ChangeSecurityQuestionsService::class);

        $changeSecurityQuestionsStepService = $serviceLocator->get(ChangeSecurityQuestionsStepService::class);

        return new ChangeSecurityQuestionOneAction(
                $changeSecurityQuestionsService,
                $changeSecurityQuestionsStepService
        );
    }
}
