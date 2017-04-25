<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Factory\Service;

use Core\Service\StepService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\AccountSummaryStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\CompletedStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\CreateAccountStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionsStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RegistrationStepServiceFactoryTest.
 *
 * @group registration
 * @group VM-11506
 */
class RegistrationStepServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $session = XMock::of(RegistrationSessionService::class);

        $serviceManager->setService(RegistrationSessionService::class, $session);

        $factory = new RegistrationStepServiceFactory();

        $this->assertInstanceOf(
            StepService::class,
            $factory->createService($serviceManager)
        );
    }

    /**
     * Test the createSteps function on an Actual StepService object.
     *
     * @throws \Exception
     */
    public function testCreateSteps()
    {
        $serviceManager = new ServiceManager();

        $session = XMock::of(RegistrationSessionService::class);

        $serviceManager->setService(RegistrationSessionService::class, $session);

        $factory = new RegistrationStepServiceFactory();

        $steps = $factory->createSteps($session);

        $this->assertCount(8, $steps);

        $this->assertInstanceOf(CreateAccountStep::class, $steps[0]);
        $this->assertInstanceOf(EmailStep::class, $steps[1]);
        $this->assertInstanceOf(DetailsStep::class, $steps[2]);
        $this->assertInstanceOf(ContactDetailsStep::class, $steps[3]);
        $this->assertInstanceOf(SecurityQuestionsStep::class, $steps[4]);
        $this->assertInstanceOf(PasswordStep::class, $steps[5]);
        $this->assertInstanceOf(AccountSummaryStep::class, $steps[6]);
        $this->assertInstanceOf(CompletedStep::class, $steps[7]);
    }
}
