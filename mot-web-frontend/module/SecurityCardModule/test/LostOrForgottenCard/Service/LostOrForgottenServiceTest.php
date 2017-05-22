<?php

namespace Dvsa\Mot\Frontend\SecurityCardTest\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\XMock;

class LostOrForgottenServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LostOrForgottenService
     */
    private $lostAndForgottenService;

    /**
     * @var UserAdminMapper
     */
    private $userAdminMapper;

    /**
     * @var LostOrForgottenSessionService
     */
    private $sessionService;

    public function setUp()
    {
        $this->userAdminMapper = XMock::of(UserAdminMapper::class);
        $this->sessionService = XMock::of(LostOrForgottenSessionService::class);
        $this->lostAndForgottenService = new LostOrForgottenService($this->userAdminMapper, $this->sessionService);
    }

    public function testGetAnswerForQuestionAnswerCorrect()
    {
        $userId = 10;
        $questionId = 1;
        $answer = 'testAnswer';

        $this->userAdminMapper
            ->expects($this->once())
            ->method('checkSecurityQuestion')
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
            ->method('checkSecurityQuestion')
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
            ->method('load')
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
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isAllowedOnStep('security-quesiton-1');

        $this->assertFalse($actual);
    }

    public function testNotAllowedOnStepWithoutSessionData()
    {
        $this->sessionService
            ->expects($this->once())
            ->method('load')
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
            ->method('load')
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

    public function testIsEnteringThroughLostAndForgottenStepInSessionShouldReturnTrue()
    {
        $steps = [
            LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isEnteringThroughAlreadyOrdered();
        $this->assertTrue($actual);
    }

    public function testIsEnteringThroughLostAndForgottenStepNotInSessionShouldReturnFalse()
    {
        $steps = [
            'test-step' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isEnteringThroughAlreadyOrdered();
        $this->assertFalse($actual);
    }

    public function testIsEnteringThroughSecurityQuestionOneStepInSessionShouldReturnTrue()
    {
        $steps = [
            LostOrForgottenCardController::LOGIN_SESSION_ROUTE => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isEnteringThroughSecurityQuestionOne();
        $this->assertTrue($actual);
    }

    public function testIsEnteringThroughSecurityQuestionOneStepNotInSessionShouldReturnFalse()
    {
        $steps = [
            'test-step' => false,
            'security-question-2' => false,
            'confirmation' => false,
        ];

        $this->sessionService
            ->expects($this->once())
            ->method('load')
            ->with(LostOrForgottenSessionService::UNIQUE_KEY)
            ->willReturn($steps);

        $actual = $this->lostAndForgottenService->isEnteringThroughSecurityQuestionOne();
        $this->assertFalse($actual);
    }

    public function testGetQuestionsForPerson()
    {
        $personId = 10;

        $this->userAdminMapper
            ->expects($this->once())
            ->method('getSecurityQuestionsForPerson')
            ->with($personId)
            ->willReturn([new SecurityQuestionDto(), new SecurityQuestionDto()]);

        $this->lostAndForgottenService->getQuestionsForPerson($personId);
    }

    public function testGetQuestionsForPerson_willThrowException()
    {
        $personId = 10;
        $expectedException = new NotFoundException(
            '/resource/path',
            0,
            new \Exception(),
            404
        );

        $this->userAdminMapper
            ->expects($this->once())
            ->method('getSecurityQuestionsForPerson')
            ->with($personId)
            ->willReturn($this->throwException($expectedException));

        $this->lostAndForgottenService->getQuestionsForPerson($personId);
    }
}
