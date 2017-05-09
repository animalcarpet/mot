<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\ReviewAction;

class ReviewController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-review';

    private $action;

    public function __construct(ReviewAction $action)
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('Confirm new record and start test', 'Make a new vehicle record');

        return $this->applyActionResult($result);
    }
}
