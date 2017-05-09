<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\ConfirmationAction;

class ConfirmationController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-created-and-started';

    private $action;

    public function __construct(ConfirmationAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->setLayout('MOT test started', 'MOT test');

        return $this->applyActionResult($result);
    }
}
