<?php


namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller;


use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;

class ChangeSecurityQuestionsController extends AbstractDvsaActionController
{
    const ROUTE = "newProfile/change-security-questions";

    private $action;

    private $changeSecurityQuestionsSessionService;

    public function __construct(ChangeSecurityQuestionsAction $action,
                                ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService)
    {
        $this->action = $action;
        $this->changeSecurityQuestionsSessionService = $changeSecurityQuestionsSessionService;
    }

    public function indexAction()
    {
        /** destroy the security questions session service when they start the journey */
        $this->changeSecurityQuestionsSessionService->clear();

        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setHeadTitle('Change security questions');
        return $this->applyActionResult($result);
    }

    protected function buildBreadcrumbs()
    {
        $this->getBreadcrumbBuilder()
            ->simple('Your profile', 'newProfile')
            ->simple('Change security questions')
            ->build();
    }
}