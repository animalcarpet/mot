<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use DvsaCommonTest\TestUtils\XMock;
use Event\Service\EventSessionService;
use Zend\InputFilter\InputFilter;

/**
 * Class SummaryStepTest.
 *
 * @group event
 */
class SummaryStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new SummaryStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(SummaryStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new SummaryStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(SummaryStep::STEP_ID, $step->getId());
    }
}
