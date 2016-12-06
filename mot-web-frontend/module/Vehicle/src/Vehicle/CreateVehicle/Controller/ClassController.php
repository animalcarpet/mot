<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\ClassAction;

class ClassController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-class';
    const PAGE_TITLE = 'What is the vehicle\'s test class?';

    private $action;

    public function __construct(ClassAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout(self::PAGE_TITLE, self::SUB_TITLE);
        return $this->applyActionResult($result);
    }
}