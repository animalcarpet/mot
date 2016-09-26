<?php


namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Action;


use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionTwoAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionForm;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use DvsaClient\Entity\SecurityQuestionSet;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class ChangeSecurityQuestionTwoActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ChangeSecurityQuestionsService $changeSecurityQuestionsSessionService */
    private $changeSecurityQuestionsService;

    /** @var  ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService */
    private $changeSecurityQuestionsStepService;

    /** @var  SecurityQuestionSet $securityQuestionSet */
    private $securityQuestionSet;

    /** @var Request $request */
    private $request;

    public function setUp()
    {
        parent::setUp();
        $this->changeSecurityQuestionsStepService = XMock::of(ChangeSecurityQuestionsStepService::class);
        $this->changeSecurityQuestionsService = XMock::of(ChangeSecurityQuestionsService::class);
        $this->request = XMock::of(Request::class);
        $this->securityQuestionSet = XMock::of(SecurityQuestionSet::class);
        $this->form = XMock::of(ChangeSecurityQuestionForm::class);
    }

    public function testWhenPost_formValid_allowedOnStep_shouldRedirectToQuestionTwo()
    {
        $this->mockIsAllowedOnStep(true);

        $this->changeSecurityQuestionsService
            ->expects($this->once())
            ->method('getSecurityQuestions')
            ->willReturn($this->mockSecurityQuestionsSet());

        $this->mockIsPost(true, $this->validPostData());

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionsReviewController::ROUTE, $actionResult->getRouteName());
    }

    public function testWhenPost_formInvalid_allowedOnStep_shouldDisplayErrors()
    {
        $this->mockIsAllowedOnStep(true);

        $this->changeSecurityQuestionsService
            ->expects($this->once())
            ->method('getSecurityQuestions')
            ->willReturn($this->mockSecurityQuestionsSet());

        $this->mockIsPost(true, $this->invalidPostData());

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $form = $actionResult->getViewModel()->getForm();
        $this->assertCount(1, $form->getMessages(ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER));
        $this->assertSame(ChangeSecurityQuestionForm::MSG_ANSWER_EMPTY,
            $form->getMessages(ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER)[0]);
    }

    public function testWhenGet_isAllowedOnStep_shouldDisplayForm()
    {
        $this->mockIsAllowedOnStep(true);

        $this->changeSecurityQuestionsService
            ->expects($this->once())
            ->method('getSecurityQuestions')
            ->willReturn($this->mockSecurityQuestionsSet());

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $form = $actionResult->getViewModel()->getForm();
        $this->assertCount(0, $form->getMessages());
    }

    public function testWhenPost_notAllowedOnStep_shouldRedirectToStartOfJourney()
    {
        $this->mockIsAllowedOnStep(false);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionOneController::ROUTE, $actionResult->getRouteName());
    }

    public function mockSecurityQuestionsSet()
    {
        $questions = [
            (new SecurityQuestionDto())->setGroup(1)->setText('questionOne')->setId(1),
            (new SecurityQuestionDto())->setGroup(1)->setText('questionTwo')->setId(2),
            (new SecurityQuestionDto())->setGroup(2)->setText('questionOne')->setId(3),
            (new SecurityQuestionDto())->setGroup(2)->setText('questionTwo')->setId(4),
        ];

        return new SecurityQuestionSet($questions);
    }

    public function mockIsAllowedOnStep($allowed)
    {
        $this->changeSecurityQuestionsStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(ChangeSecurityQuestionsStepService::QUESTION_TWO_STEP)
            ->willReturn($allowed);
    }

    public function mockIsPost($isPost, $postData)
    {
        if ($isPost) {
            $params = XMock::of(ParametersInterface::class);
            $params->expects($this->once())
                ->method('toArray')
                ->willReturn($postData);

            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
            $this->request->expects($this->once())->method('getPost')->willReturn($params);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }

    public function buildAction()
    {
        $action = new ChangeSecurityQuestionTwoAction(
            $this->changeSecurityQuestionsService,
            $this->changeSecurityQuestionsStepService
        );
        return $action;
    }

    public function validPostData()
    {
        return [
            'questions' => 3,
            'question-answer' => 'bacon',
        ];
    }

    public function invalidPostData()
    {
        return [
            'questions' => null,
            'question-answer' => null,
        ];
    }
}