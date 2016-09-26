<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsReviewAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsReviewActionFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $changeSecurityQuestionsStepService = $serviceLocator->get(ChangeSecurityQuestionsStepService::class);

        $changeSecurityQuestionsService = $serviceLocator->get(ChangeSecurityQuestionsService::class);

        return new ChangeSecurityQuestionsReviewAction(
            $changeSecurityQuestionsStepService,
            $changeSecurityQuestionsService
        );
    }
}