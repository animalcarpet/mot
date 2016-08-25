<?php

namespace Dvsa\Mot\Frontend\SecurityCardTest\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\XMock;

class LostOrForgottenServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LostOrForgottenService $lostAndForgottenService
     */
    private $lostAndForgottenService;

    /**
     * @var UserAdminMapper $userAdminMapper
     */
    private $userAdminMapper;

    /**
     * @var LostOrForgottenSessionService $sessionService
     */
    private $sessionService;

    public function setUp()
    {
        $this->userAdminMapper = XMock::of(UserAdminMapper::class);
        $this->sessionService = XMock::of(LostOrForgottenSessionService::class);
        $this->lostAndForgottenService = new LostOrForgottenService($this->userAdminMapper, $this->sessionService);
    }

    public function testGetQuestionForUserQuestionFound()
    {
        $userId = 10;
        $questionId = 1;

        $this->userAdminMapper
            ->expects($this->once())
            ->method("getSecurityQuestion")
            ->with($questionId, $userId)
            ->WillReturn(new SecurityQuestionDto());

        $actual = $this->lostAndForgottenService->getQuestionForUser($questionId, $userId);

        $this->assertInstanceOf(SecurityQuestionDto::class, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetQuestionForUserNoQuestionFoundExceptionThrown()
    {
        $userId = 10;
        $questionId = 1;

        $this->userAdminMapper
            ->expects($this->once())
            ->method("getSecurityQuestion")
            ->with($questionId, $userId)
            ->will($this->throwException(new NotFoundException('/', 'post', [], 10, 'Question not found')));

        $this->lostAndForgottenService->getQuestionForUser($questionId, $userId);
    }

    public function testGetAnswerForQuestionAnswerCorrect()
    {
        $userId = 10;
        $questionId = 1;
        $answer = 'testAnswer';

        $this->userAdminMapper
            ->expects($this->once())
            ->method("checkSecurityQuestion")
            ->with($questionId, $userId, ['answer' => $answer])
            ->WillReturn(true);

        $actual = $this->lostAndForgottenService->getAnswerForQuestion($questionId, $userId, $answer);

        $this->assertTrue($actual);
    }

    public function testGetAnswerForQuestionAnswerIncorrect()
    {
        $userId = 10;
        $questionId = 1;
        $answer = 'testAnswer';

        $this->userAdminMapper
            ->expects($this->once())
            ->method("checkSecurityQuestion")
            ->with($questionId, $userId, ['answer' => $answer])
            ->WillReturn(false);

        $actual = $this->lostAndForgottenService->getAnswerForQuestion($questionId, $userId, $answer);

        $this->assertFalse($actual);
    }

    public function testAllowedOnStepWithValidPreviousStep()
    {
        $steps = [
            'intro' => true,
            'security-quesiton-1' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method("load")
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isAllowedOnStep('security-quesiton-1');

        $this->assertTrue($actual);
    }

    public function testNotAllowedOnStepWithInvalidPreviousStep()
    {
        $steps = [
            'intro' => false,
            'security-quesiton-1' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method("load")
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isAllowedOnStep('security-quesiton-1');

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepWithoutSessionData()
    {
        $this->sessionService
            ->expects($this->once())
            ->method("load")
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn(null);

        $actual = $this->lostAndForgottenService->isAllowedOnStep('security-quesiton-1');

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepIfStepDoesNotExistInSession()
    {
        $steps = [
            'intro' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method("load")
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isAllowedOnStep('security-quesiton-1');

        $this->assertFalse($actual);
    }

    public function testSaveSteps()
    {
        $steps = [
            'intro' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('save')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY, $steps);

        $this->lostAndForgottenService->saveSteps($steps);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Steps are not stored in session
     */
    public function testUpdateStepStatusNoStepDataInSession()
    {
        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn([]);

        $this->lostAndForgottenService->updateStepStatus('test-step', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step: test-step is not a valid step
     */
    public function testUpdateStepStatusStepNameIsNotValid()
    {
        $steps = [
            'intro' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->lostAndForgottenService->updateStepStatus('test-step', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Step status must be a boolean
     */
    public function testUpdateStepStatusStepStatusIsNotABoolean()
    {
        $steps = [
            'intro' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->lostAndForgottenService->updateStepStatus('intro', 'true');
    }

    public function testUpdateStepStatusUpdatesSucessfully()
    {
        $stepName = 'intro';
        $stepStatus = true;

        $steps = [
            $stepName => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $stepResult = [
            $stepName => true,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $this->sessionService
            ->expects($this->once())
            ->method('save')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY, $stepResult);

        $this->lostAndForgottenService->updateStepStatus($stepName, $stepStatus);
    }

    public function testClearSession()
    {
        $this->sessionService
            ->expects($this->once())
            ->method('clear');

        $this->lostAndForgottenService->clearSession();
    }
}