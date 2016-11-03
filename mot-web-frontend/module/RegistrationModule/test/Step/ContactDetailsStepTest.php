<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

/**
 * Class ContactDetailsStepTest.
 *
 * @group VM-11506
 */
class ContactDetailsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new ContactDetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(ContactDetailsStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new ContactDetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(ContactDetailsStep::STEP_ID, $step->getId());
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
            ->with(ContactDetailsStep::STEP_ID)
            ->willReturn($fixture);

        $step = new ContactDetailsStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getAddress1(), $fixture['address1']);
        $this->assertEquals($step->getAddress2(), $fixture['address2']);
        $this->assertEquals($step->getAddress3(), $fixture['address3']);
        $this->assertEquals($step->gettownOrCity(), $fixture['townOrCity']);
        $this->assertEquals($step->getpostcode(), strtoupper($fixture['postcode']));
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new ContactDetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setAddress1('address1');
        $step->setAddress2('address2');
        $step->setAddress3('address3');
        $step->settownOrCity('townOrCity');
        $step->setpostcode('postcode');
        $step->setPhone('12345678');

        $values = $step->toArray();

        $this->assertEquals('address1', $values['address1']);
        $this->assertEquals('address2', $values['address2']);
        $this->assertEquals('address3', $values['address3']);
        $this->assertEquals('townOrCity', $values['townOrCity']);
        $this->assertEquals('postcode', $values['postcode']);
        $this->assertEquals('12345678', $values['phone']);
    }

    /**
     * Test all the property getters and setters.
     */
    public function testGettersSetters()
    {
        $step = new ContactDetailsStep(
            XMock::of(RegistrationSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setAddress1('address1');
        $step->setAddress2('address2');
        $step->setAddress3('address3');
        $step->settownOrCity('townOrCity');
        $step->setpostcode('postcode');
        $step->setPhone('12345678');

        $this->assertEquals('address1', $step->getAddress1());
        $this->assertEquals('address2', $step->getAddress2());
        $this->assertEquals('address3', $step->getAddress3());
        $this->assertEquals('townOrCity', $step->gettownOrCity());
        $this->assertEquals('postcode', $step->getpostcode());
        $this->assertEquals('12345678', $step->getPhone());
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            'address1'   => __METHOD__ . '_address1',
            'address2'   => __METHOD__ . '_address2',
            'address3'   => __METHOD__ . '_address3',
            'townOrCity' => __METHOD__ . '_townOrCity',
            'postcode'   => __METHOD__ . '_postcode',
            'phone'      => __METHOD__ . '_phone',
        ];

        return $fixture;
    }
}
