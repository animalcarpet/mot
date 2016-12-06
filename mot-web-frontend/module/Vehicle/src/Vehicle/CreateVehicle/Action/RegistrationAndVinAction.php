<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Vehicle\CreateVehicle\Controller\MakeController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Controller\StartController;
use Vehicle\CreateVehicle\Form\RegistrationAndVinForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class RegistrationAndVinAction
{
    private $createVehicleStepService;

    public function __construct(CreateVehicleStepService $createVehicleStepService)
    {
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute(Request $request)
    {
        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REG_VIN_STEP)) {
            return new RedirectToRoute(StartController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $sessionData = $this->createVehicleStepService->getStep('reg-vin');
        $registrationFieldValue = $sessionData['reg-input'];
        $registrationCheckboxValue = $sessionData['leavingRegBlank'];
        $vinFieldValue = $sessionData['vin-input'];
        $vinCheckboxValue = $sessionData['leavingVINBlank'];

        $form = new RegistrationAndVinForm(trim($registrationFieldValue), $registrationCheckboxValue, trim($vinFieldValue), $vinCheckboxValue);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->getRegistrationField()->setValue(trim($postData['reg-input']));
            $form->getVINField()->setValue(trim($postData['vin-input']));
            $form->getRegistrationCheckbox()->setAttribute('checked', $postData['leavingRegBlank']);
            $form->getVINCheckbox()->setAttribute('checked', $postData['leavingVINBlank']);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::REG_VIN_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::REG_VIN_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(MakeController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/registrationAndVin.twig')
            ->setViewModel($viewModel);
    }
}