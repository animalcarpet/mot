<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsReviewAction;

class ChangeSecurityQuestionsReviewController extends AbstractDvsaActionController
{
    const ROUTE = 'newProfile/change-security-questions/review';

    private $action;

    public function __construct(ChangeSecurityQuestionsReviewAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setHeadTitle('Review security question changes');

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
