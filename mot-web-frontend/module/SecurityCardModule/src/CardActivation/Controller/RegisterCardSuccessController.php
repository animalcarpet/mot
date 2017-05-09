<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardSuccessAction;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class RegisterCardSuccessController extends AbstractDvsaActionController implements AutoWireableInterface
{
    /** @var RegisterCardSuccessAction */
    private $action;

    public function __construct(RegisterCardSuccessAction $action)
    {
        $this->action = $action;
    }

    /**
     * Redirect to Success Page.
     */
    public function successAction()
    {
        return $this->applyActionResult($this->action->execute($this->getRequest()));
    }
}
