<?php

namespace Vehicle\CreateVehicle\Controller;

use Vehicle\CreateVehicle\Action\CountryOfRegistrationAction;

class CountryOfRegistrationController extends BaseCreateVehicleController
{
    const ROUTE = 'create-vehicle/new-vehicle-country-of-reg';

    private $action;

    public function __construct(
        CountryOfRegistrationAction $action
    ) {
        $this->action = $action;
    }

    public function indexAction()
    {
        $result = $this->action->execute($this->getRequest());
        $this->buildBreadcrumbs();
        $this->setLayout('What is the vehicle\'s country of registration?', self::SUB_TITLE);

        return $this->applyActionResult($result);
    }
}
