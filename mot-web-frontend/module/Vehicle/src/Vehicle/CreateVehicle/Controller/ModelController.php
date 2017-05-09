<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\ModelAction;

class ModelController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-model';

    private $action;

    public function __construct(
        ModelAction $action
    ) {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('What is the vehicle\'s model?', self::SUB_TITLE);

        return $this->applyActionResult($result);
    }
}
