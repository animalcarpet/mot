<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

/**
 * Class PasswordStepTest.
 *
 * @group VM-11506
 */
class PasswordStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new PasswordStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(PasswordStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new PasswordStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(PasswordStep::STEP_ID, $step->getId());
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
            ->with(PasswordStep::STEP_ID)
            ->willReturn($fixture);

        $step = new PasswordStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getPassword(), $fixture['password']);
        $this->assertEquals($step->getPasswordConfirm(), $fixture['passwordConfirm']);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new PasswordStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setPassword('password');
        $step->setPasswordConfirm('passwordConfirm');

        $values = $step->toArray();

        $this->assertEquals('password', $values['password']);
        $this->assertEquals('passwordConfirm', $values['passwordConfirm']);
    }

    /**
     * Test all the property getters and setters.
     */
    public function testGettersSetters()
    {
        $step = new PasswordStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setPassword('password');
        $step->setPasswordConfirm('passwordConfirm');

        $this->assertEquals('password', $step->getPassword());
        $this->assertEquals('passwordConfirm', $step->getPasswordConfirm());
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            'password' => __METHOD__.'_password',
            'passwordConfirm' => __METHOD__.'_passwordConfirm',
        ];

        return $fixture;
    }
}
