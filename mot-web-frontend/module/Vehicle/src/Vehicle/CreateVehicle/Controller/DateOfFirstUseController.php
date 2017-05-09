<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\DateOfFirstUseAction;

class DateOfFirstUseController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-first-use-date';

    private $action;

    public function __construct(DateOfFirstUseAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->setLayout('What is the vehicle\'s date of first use?', self::SUB_TITLE);
        $this->buildBreadcrumbs();

        return $this->applyActionResult($result);
    }
}
