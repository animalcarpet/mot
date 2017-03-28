<?php


namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller;


use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsConfirmationAction;

class ChangeSecurityQuestionsConfirmationController extends AbstractDvsaActionController
{
    const ROUTE = "newProfile/change-security-questions/confirmation";

    private $action;

    public function __construct(ChangeSecurityQuestionsConfirmationAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute();
        $this->buildBreadcrumbs();
        $this->setHeadTitle('Your security questions have been changed');
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