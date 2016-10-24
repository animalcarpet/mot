<?php

namespace Vehicle\UpdateVehicleProperty\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\UpdateVehicleProperty\Action\UpdateColourAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateCountryAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateEngineAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateClassAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateFirstUsedDateAction;
use Vehicle\UpdateVehicleProperty\Action\UpdateMakeAndModelAction;

class UpdateVehiclePropertyController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $updateCountryAction;
    private $updateClassAction;
    private $updateEngineAction;
    private $updateFirstUsedDateAction;
    private $updateMakeAndModelAction;
    private $updateColourAction;

    public function __construct(
        UpdateEngineAction $updateEngineAction,
        UpdateCountryAction $updateCountryAction,
        UpdateClassAction $updateClassAction,
        UpdateFirstUsedDateAction $updateFirstUsedDateAction,
        UpdateMakeAndModelAction $updateMakeAndModelAction,
        UpdateColourAction $updateColourAction
    )
    {
        $this->updateEngineAction = $updateEngineAction;
        $this->updateCountryAction = $updateCountryAction;
        $this->updateClassAction = $updateClassAction;
        $this->updateFirstUsedDateAction = $updateFirstUsedDateAction;
        $this->updateMakeAndModelAction = $updateMakeAndModelAction;
        $this->updateColourAction = $updateColourAction;
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

    public function editFirstUsedDateAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();

        $actionResult = $this->updateFirstUsedDateAction->execute($isPost, $vehicleId, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function editMakeAndModelAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();
        $property = $this->getProperty();
        $formUuid = $this->getFormUuid();

        $actionResult = $this->updateMakeAndModelAction->execute($property, $vehicleId, $isPost, $formUuid, $formData);

        return $this->applyActionResult($actionResult);
    }

    public function editColourAction()
    {
        $isPost = $this->requestIsPost();
        $vehicleId = $this->getVehicleId();
        $formData = $this->getFormData();

        $actionResult = $this->updateColourAction->execute($isPost, $vehicleId, $formData);

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
     * @return string
     */
    private function getProperty()
    {
        return $this->params()->fromRoute('property');
    }

    /**
     * @return string
     */
    private function getFormUuid()
    {
        return $this->params()->fromQuery("formUuid");
    }

    /**
     * @return bool
     */
    private function requestIsPost()
    {
        return $this->getRequest()->isPost();
    }
}