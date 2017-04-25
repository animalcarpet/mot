<?php

namespace Vehicle\CreateVehicle\Controller;

use Core\Controller\AbstractDvsaActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

abstract class BaseCreateVehicleController extends AbstractDvsaActionController
{
    const SUB_TITLE = 'Make a new vehicle record';

    protected function buildBreadcrumbs()
    {
        $this->getBreadcrumbBuilder()
            ->simple('MOT test', 'vehicle-search')
            ->simple('Make a new vehicle record')
            ->build();
    }

    protected function setLayout($pageTitle, $pageSubtitle)
    {
        $this->layout('layout/layout-govuk.phtml');
        $this
            ->layout()
            ->setVariable('pageTitle', $pageTitle)
            ->setVariable('pageSubTitle', $pageSubtitle);
        $this->setHeadTitle($pageTitle);
    }
}