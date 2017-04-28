<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;

class RegisterCardHardStopController extends AbstractDvsaActionController
{
    private $action;

    public function __construct(RegisterCardHardStopAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->setHeadTitle('Activate your security card now');
        $this->layout()->setVariable('pageTitle', ''); /* design required empty title */

        return $this->applyActionResult($this->action->execute());
    }
}
