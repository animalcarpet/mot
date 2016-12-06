<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\ColourAction;

class ColourController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-colour';

    private $action;

    public function __construct(ColourAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('What is the vehicle\'s colour?', 'Make a new vehicle record');

        return $this->applyActionResult($result);
    }
}