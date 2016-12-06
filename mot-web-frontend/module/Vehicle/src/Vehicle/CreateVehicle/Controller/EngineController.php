<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\EngineAction;

class EngineController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-engine';

    private $action;

    public function __construct(EngineAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->setLayout('Engine and fuel type', self::SUB_TITLE);
        $this->buildBreadcrumbs();

        return $this->applyActionResult($result);
    }
}