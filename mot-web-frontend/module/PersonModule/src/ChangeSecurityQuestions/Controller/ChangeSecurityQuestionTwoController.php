<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionTwoAction;

class ChangeSecurityQuestionTwoController extends AbstractDvsaActionController
{
    const ROUTE = "newProfile/change-security-questions/question-two";

    private $action;

    public function __construct(ChangeSecurityQuestionTwoAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
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