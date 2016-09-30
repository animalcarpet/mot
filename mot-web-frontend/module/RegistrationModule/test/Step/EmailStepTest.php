<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

class EmailStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new EmailStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(EmailStep::class, $step);
    }

    public function testId()
    {
        $step = new EmailStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(EmailStep::STEP_ID, $step->getId());
    }

    /**
     * Test loading data returned from the session.
     *
     * @throws \Exception
     */
    public function testLoad()
    {
        $fixture = $this->getFixture();

        $session = XMock::of(RegistrationSessionService::class);
        $session->expects($this->once())
            ->method('load')
            ->with(EmailStep::STEP_ID)
            ->willReturn($fixture);

        $step = new EmailStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getEmailAddress(), $fixture['emailAddress']);
        $this->assertEquals($step->getConfirmEmailAddress(), $fixture['confirmEmailAddress']);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new EmailStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setEmailAddress('email@dvsatest.com');
        $step->setConfirmEmailAddress('email1@dvsatest.com');

        $values = $step->toArray();

        $this->assertEquals('email@dvsatest.com', $values['emailAddress']);
        $this->assertEquals('email1@dvsatest.com', $values['confirmEmailAddress']);
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            'emailAddress'             =>  'email@dvsatest.com',
            'confirmEmailAddress'      =>  'email1@dvsatest.com',
        ];

        return $fixture;
    }
}
