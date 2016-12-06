<?php

namespace Vehicle\CreateVehicle\Controller;

use Core\Controller\AbstractDvsaActionController;
use Vehicle\CreateVehicle\Action\RegistrationAndVinAction;

class RegistrationAndVinController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-vrm-and-vin';

    private $action;

    public function __construct(
        RegistrationAndVinAction $action
    )
    {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('What are the vehicle\'s registration mark and VIN?', self::SUB_TITLE);

        return $this->applyActionResult($result);
    }
}