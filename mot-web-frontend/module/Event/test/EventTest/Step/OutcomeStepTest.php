<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use DvsaCommon\InputFilter\Event\OutcomeInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use Event\Service\EventSessionService;
use Zend\InputFilter\InputFilter;

/**
 * Class OutcomeStepTest.
 *
 * @group event
 */
class OutcomeStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new OutcomeStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(OutcomeStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new OutcomeStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(OutcomeStep::STEP_ID, $step->getId());
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
            ->with(OutcomeStep::STEP_ID)
            ->willReturn($fixture);

        $step = new OutcomeStep(
            $session,
            XMock::of(InputFilter::class)
        );

        $step->load();

        $this->assertEquals($step->getOutcomeCode(), $fixture[OutcomeInputFilter::FIELD_OUTCOME]);
        $this->assertEquals($step->getNotes(), $fixture[OutcomeInputFilter::FIELD_NOTES]);
    }

    /**
     * Test extracting values into an array.
     */
    public function testToArray()
    {
        $step = new OutcomeStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $step->setOutcomeCode(OutcomeInputFilter::FIELD_OUTCOME);
        $step->setNotes(OutcomeInputFilter::FIELD_NOTES);

        $values = $step->toArray();

        $this->assertEquals(OutcomeInputFilter::FIELD_OUTCOME, $values[OutcomeInputFilter::FIELD_OUTCOME]);
        $this->assertEquals(OutcomeInputFilter::FIELD_NOTES, $values[OutcomeInputFilter::FIELD_NOTES]);
    }

    /**
     * @return array
     */
    public function getFixture()
    {
        $fixture = [
            OutcomeInputFilter::FIELD_OUTCOME => __METHOD__.'_outcomeCode',
            OutcomeInputFilter::FIELD_NOTES => __METHOD__.'_notes',
        ];

        return $fixture;
    }
}
