<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

/**
 * Class DetailsStepTest.
 *
 * @group VM-11506
 */
class DetailsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new DetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(DetailsStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new DetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(DetailsStep::STEP_ID, $step->getId());
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
            ->with(DetailsStep::STEP_ID)
            ->willReturn($fixture);

        $step = new DetailsStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getFirstName(), $fixture['firstName']);
        $this->assertEquals($step->getMiddleName(), $fixture['middleName']);
        $this->assertEquals($step->getLastName(), $fixture['lastName']);
        $this->assertEquals($step->getPhone(), $fixture['phone']);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new DetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setFirstName('firstName');
        $step->setMiddleName('middleName');
        $step->setLastName('lastName');
        $step->setPhone('phone');

        $values = $step->toArray();

        $this->assertEquals('firstName', $values['firstName']);
        $this->assertEquals('middleName', $values['middleName']);
        $this->assertEquals('lastName', $values['lastName']);
        $this->assertEquals('phone', $values['phone']);
    }

    /**
     * Test all the property getters and setters.
     */
    public function testGettersSetters()
    {
        $step = new DetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setFirstName('firstName');
        $step->setMiddleName('middleName');
        $step->setLastName('lastName');
        $step->setPhone('phone');

        $this->assertEquals('firstName', $step->getFirstName());
        $this->assertEquals('middleName', $step->getMiddleName());
        $this->assertEquals('lastName', $step->getLastName());
        $this->assertEquals('phone', $step->getPhone());
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            'firstName'             => __METHOD__ . '_firstName',
            'middleName'            => __METHOD__ . '_middleName',
            'lastName'              => __METHOD__ . '_lastName',
            'phone'                 => __METHOD__ . '_phone',
        ];

        return $fixture;
    }
}
