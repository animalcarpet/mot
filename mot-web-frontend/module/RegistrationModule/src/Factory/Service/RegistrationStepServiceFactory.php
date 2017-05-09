<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Service;

use Core\Factory\StepServiceFactory;
use Core\Service\SessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\AccountSummaryStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\CompletedStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\CreateAccountStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionsStep;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegistrationStepServiceFactory extends StepServiceFactory
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RegistrationStepService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->sessionService = $serviceLocator->get(RegistrationSessionService::class);

        $steps = $this->createSteps($this->sessionService);

        return new RegistrationStepService($steps);
    }

    /**
     * @return array
     */
    public function createSteps(SessionService $sessionService)
    {
        $steps = [
            new CreateAccountStep($sessionService, new InputFilter()),
            new EmailStep($sessionService, new EmailInputFilter()),
            new DetailsStep($sessionService, new DetailsInputFilter()),
            new ContactDetailsStep($sessionService, new ContactDetailsInputFilter()),
            new SecurityQuestionsStep($sessionService, new SecurityQuestionsInputFilter()),
            new PasswordStep($sessionService, new PasswordInputFilter()),
            new AccountSummaryStep($sessionService, new InputFilter()),
            new CompletedStep($sessionService, new InputFilter()),
        ];

        return $steps;
    }
}
