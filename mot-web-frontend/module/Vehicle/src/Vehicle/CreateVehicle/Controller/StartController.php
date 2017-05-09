<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\StartAction;

class StartController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle';

    private $action;

    public function __construct(
        StartAction $action
    ) {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute();
        $this->buildBreadcrumbs();
        $this->setLayout('Make a new vehicle record', '');

        return $this->applyActionResult($result);
    }
}
