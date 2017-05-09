<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use DvsaCommon\InputFilter\Event\RecordInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use Event\Service\EventSessionService;
use Zend\InputFilter\InputFilter;

/**
 * Class RecordStepTest.
 *
 * @group event
 */
class RecordStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new RecordStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(RecordStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new RecordStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(RecordStep::STEP_ID, $step->getId());
    }

    /**
     * Test loading data returned from the session.
     *
     * @throws \Exception
     */
    public function testLoad()
    {
        $fixture = $this->getFixture();

        $session = XMock::of(EventSessionService::class);
        $session->expects($this->once())
            ->method('load')
            ->with(RecordStep::STEP_ID)
            ->willReturn($fixture);

        $step = new RecordStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getEventType(), $fixture[RecordInputFilter::FIELD_TYPE]);
        $this->assertEquals($step->getDate(), $fixture[RecordInputFilter::FIELD_DATE]);
        $this->assertEquals($step->getDay(), $fixture[RecordInputFilter::FIELD_DAY]);
        $this->assertEquals($step->getMonth(), $fixture[RecordInputFilter::FIELD_MONTH]);
        $this->assertEquals($step->getYear(), $fixture[RecordInputFilter::FIELD_YEAR]);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new RecordStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setEventType(RecordInputFilter::FIELD_TYPE);
        $step->setDay(RecordInputFilter::FIELD_DAY);
        $step->setMonth(RecordInputFilter::FIELD_MONTH);
        $step->setYear(RecordInputFilter::FIELD_YEAR);

        $values = $step->toArray();

        $this->assertEquals(RecordInputFilter::FIELD_TYPE, $values[RecordInputFilter::FIELD_TYPE]);
        $this->assertEquals(RecordInputFilter::FIELD_TYPE, $values[RecordInputFilter::FIELD_TYPE]);
        $this->assertEquals(RecordInputFilter::FIELD_TYPE, $values[RecordInputFilter::FIELD_TYPE]);
        $this->assertEquals(RecordInputFilter::FIELD_TYPE, $values[RecordInputFilter::FIELD_TYPE]);
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            RecordInputFilter::FIELD_TYPE => __METHOD__.'_eventType',
            RecordInputFilter::FIELD_DATE => __METHOD__.'_date',
            RecordInputFilter::FIELD_DAY => '09',
            RecordInputFilter::FIELD_MONTH => '09',
            RecordInputFilter::FIELD_YEAR => 2015,
        ];

        return $fixture;
    }
}
