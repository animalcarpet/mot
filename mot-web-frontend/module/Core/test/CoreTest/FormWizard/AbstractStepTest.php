<?php
namespace CoreTest\FormWizard;

use Core\FormWizard\AbstractStep;
use CoreTest\FormWizard\Fake\FakeStep;

class AbstractStepTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractStep
     */
    private $stepOne;

    /**
     * @var AbstractStep
     */
    private $stepTwo;

    public function setUp()
    {
        $stepOne = new FakeStep("first step", true, []);
        $stepTwo = new FakeStep("second step", true, []);

        $stepOne->setNextStep($stepTwo);
        $stepTwo->setPrevStep($stepOne);

        $this->stepOne = $stepOne;
        $this->stepTwo = $stepTwo;
    }

    public function testHasPrevStepReturnsFalseIfDoesNotHavePreviousStep()
    {
         $this->assertFalse($this->stepOne->hasPrevStep());
    }

    public function tesGetPrevStepReturnsNullIfDoesNotHavePreviousStep()
    {
        $this->assertNull($this->stepOne->getPrevStep());
    }

    public function testHasPrevStepReturnsTrue()
    {
        $this->assertTrue($this->stepTwo->hasPrevStep());
    }

    public function testGetPrevStepReturnsStep()
    {
        $this->assertEquals($this->stepOne, $this->stepTwo->getPrevStep());
    }

    public function testHasNextReturnsFalseIfDoesNotHaveNextStep()
    {
        $this->assertFalse($this->stepTwo->hasNextStep());
    }

    public function testGetNextReturnsNullIfDoesNotHaveNextStep()
    {
        $this->assertNull($this->stepTwo->getNextStep());
    }

    public function testHasNextStepReturnsTrue()
    {
        $this->assertTrue($this->stepOne->hasNextStep());
    }

    public function testGetNextStepReturnsStep()
    {
        $this->assertEquals($this->stepTwo, $this->stepOne->getNextStep());
    }
}
