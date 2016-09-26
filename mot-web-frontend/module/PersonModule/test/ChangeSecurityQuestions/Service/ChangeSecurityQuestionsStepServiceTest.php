<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use DvsaCommonTest\TestUtils\XMock;

class ChangeSecurityQuestionsStepServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService */
    private $changeSecurityQuestionsSessionService;

    public function setUp()
    {
        parent::setUp();
        $this->changeSecurityQuestionsSessionService = XMock::of(ChangeSecurityQuestionsSessionService::class);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step: random-test-name is not a valid step
     */
    public function testUpdateStepStatusWithInvalidStepName_shouldThrowAnException()
    {
        $steps = $this->mockPreviousStepsStatus();

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($steps);

        $this->buildStepService()->updateStepStatus('random-test-name', true);
    }

    public function testUpdateStepStatusWithValidStepName()
    {
        $steps = $this->mockPreviousStepsStatus();

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($steps);

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('save')
            ->willReturn(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $steps);

        $this->buildStepService()->updateStepStatus('start', true);
    }

    public function testUpdateQuestionTwoWithValidQuestionNumber()
    {
        $expected = $this->mockQuestionsSessionStoreQuestionTwo();
        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn([ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES => [] ]);

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('save')
            ->with(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $expected);

        $this->buildStepService()->updateQuestion(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP, 1, 'cheese', 'ham');
    }

    public function testUpdateQuestionOneWithValidQuestionNumber()
    {
        $expected = $this->mockQuestionsSessionStoreQuestionOne();
        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn([ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES => [] ]);

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('save')
            ->with(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $expected);

        $this->buildStepService()->updateQuestion(ChangeSecurityQuestionsStepService::QUESTION_ONE_STEP, 1, 'cheese', 'ham');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step status must be a boolean
     */
    public function testUpdateStepStatusWithNonBoolean_shouldThrowAnError()
    {
        $steps = $this->mockPreviousStepsStatus();

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($steps);

        $this->buildStepService()->updateStepStatus('question-one', 'dsfd');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Steps are not stored in session
     */
    public function testUpdateStepStatusWithNoSessionStarted_shouldThrowAnError()
    {
        $this->buildStepService()->updateStepStatus('question-one', 'dsfd');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage question number not valid
     */
    public function testUpdateQuestionWithInvalidQuestionNumber_shouldThrowAnException()
    {
        $this->buildStepService()->updateQuestion(3, 3, 'f', 'f');
    }

    public function testIsAllowedOnStepWithNoSessionStepsLoaded_shouldNotBeAllowedOnStep()
    {
        $actual = $this->buildStepService()->isAllowedOnStep(ChangeSecurityQuestionsStepService::REVIEW_STEP);

        $this->assertFalse($actual);
    }

    public function testIsAllowedOnStepWithOutTheSessionStepIdLoaded_shouldNotBeAllowedOnStep()
    {
        $stages = [
            ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE => [
                ChangeSecurityQuestionsStepService::START_STEP => true,
                ChangeSecurityQuestionsStepService::QUESTION_ONE_STEP => true,
            ],
        ];

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($stages);

        $actual = $this->buildStepService()->isAllowedOnStep(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP);

        $this->assertFalse($actual);
    }

    public function testIsAllowedOnStepWithValidPreviousStep_shouldBeAllowedOnTheStep()
    {
        $stages = $this->mockPreviousStepsStatus(true, true);

        $this->changeSecurityQuestionsSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($stages);

        $actual = $this->buildStepService()->isAllowedOnStep(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP);

        $this->assertTrue($actual);
    }

    public function testGetStepsReturnsCorrectSteps()
    {
        $this->assertSame($this->mockSteps(), $this->buildStepService()->getSteps());
    }

    public function buildStepService()
    {
        $stepService = new ChangeSecurityQuestionsStepService(
            $this->changeSecurityQuestionsSessionService
        );
        return $stepService;
    }

    public function mockSteps()
    {
        return [
            ChangeSecurityQuestionsStepService::START_STEP,
            ChangeSecurityQuestionsStepService::QUESTION_ONE_STEP,
            ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP,
            ChangeSecurityQuestionsStepService::REVIEW_STEP,
            ChangeSecurityQuestionsStepService::CONFIRMATION_STEP,
        ];
    }

    public function mockQuestionsSessionStoreQuestionOne()
    {
        $steps = [
            ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES => [
                'questionOneId' => 1,
                'questionOneText' => 'cheese',
                'questionOneAnswer' => 'ham',
            ],
        ];
        return $steps;
    }


    public function mockQuestionsSessionStoreQuestionTwo()
    {
        $steps = [
            ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES => [
                'questionTwoId' => 1,
                'questionTwoText' => 'cheese',
                'questionTwoAnswer' => 'ham',
            ],
        ];
        return $steps;
    }

    public function mockPreviousStepsStatus($stepOne = false, $stepTwo = false, $stepThree = false, $stepFour = false, $stepFive = false)
    {
        $steps = [
            ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE => [
                ChangeSecurityQuestionsStepService::START_STEP => $stepOne,
                ChangeSecurityQuestionsStepService::QUESTION_ONE_STEP => $stepTwo,
                ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP => $stepThree,
                ChangeSecurityQuestionsStepService::REVIEW_STEP => $stepFour,
                ChangeSecurityQuestionsStepService::CONFIRMATION_STEP => $stepFive,
            ],
        ];
        return $steps;
    }
}