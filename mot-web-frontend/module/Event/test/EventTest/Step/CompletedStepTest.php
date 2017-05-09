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
 * Class CompletedStepTest.
 *
 * @group event
 */
class CompletedStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor.
     *
     * @throws \Exception
     */
    public function testConstructor()
    {
        $step = new CompletedStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertInstanceOf(CompletedStep::class, $step);
    }

    /**
     * Placeholder test until validation stories are implemented.
     */
    public function testId()
    {
        $step = new CompletedStep(
            XMock::of(EventSessionService::class),
            XMock::of(InputFilter::class)
        );

        $this->assertEquals(CompletedStep::STEP_ID, $step->getId());
    }
}
