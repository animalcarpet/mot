<?php


namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller;


use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionOneAction;

class ChangeSecurityQuestionOneController extends AbstractDvsaActionController
{
    const ROUTE = "newProfile/change-security-questions/question-one";

    private $action;

    public function __construct(ChangeSecurityQuestionOneAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setHeadTitle('First security question');
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