<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\MakeAction;

class MakeController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-make';

    private $action;

    public function __construct(
        MakeAction $action
    ) {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('What is the vehicle\'s make?', self::SUB_TITLE);

        return $this->applyActionResult($result);
    }
}
