<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeSecurityQuestionsStepServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService */
        $changeSecurityQuestionsSessionService = $serviceLocator->get(ChangeSecurityQuestionsSessionService::class);

        return new ChangeSecurityQuestionsStepService($changeSecurityQuestionsSessionService);
    }
}
