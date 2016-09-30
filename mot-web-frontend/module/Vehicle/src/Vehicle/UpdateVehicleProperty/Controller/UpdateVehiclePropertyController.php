<?php

namespace Vehicle\UpdateVehicleProperty\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\UpdateVehicleProperty\Action\UpdateCountryAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateEngineAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateClassAction;

class UpdateVehiclePropertyController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $updateCountryAction;
    private $updateClassAction;
    private $updateEngineAction;

    public function __construct(
        UpdateEngineAction $updateEngineAction,
        UpdateCountryAction $updateCountryAction,
        UpdateClassAction $updateClassAction
    )
    {
        $this->updateEngineAction = $updateEngineAction;
        $this->updateCountryAction = $updateCountryAction;
        $this->updateClassAction = $updateClassAction;
    }

    public function editEngineAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();

        $actionResult = $this->updateEngineAction->execute($isPost, $vehicleId, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function editCountryAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();

        $actionResult = $this->updateCountryAction->execute($isPost, $vehicleId, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function editClassAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();

        $actionResult = $this->updateClassAction->execute($isPost, $vehicleId, $formData);

        return $this->applyActionResult($actionResult);
    }

    /**
     * @return array
     */
    private function getFormData()
    {
        return $this->getRequest()->getPost()->getArrayCopy();
    }

    /**
     * @return string
     */
    private function getVehicleId()
    {
        return $this->params()->fromRoute('id');
    }

    /**
     * @return bool
     */
    private function requestIsPost()
    {
        return $this->getRequest()->isPost();
    }
}