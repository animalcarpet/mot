<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardHardStopAction;
use Zend\View\Model\ViewModel;

class RegisterCardHardStopController extends AbstractDvsaActionController
{
    private $action;

    public function __construct(RegisterCardHardStopAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        return $this->applyActionResult($this->action->execute());
    }
}