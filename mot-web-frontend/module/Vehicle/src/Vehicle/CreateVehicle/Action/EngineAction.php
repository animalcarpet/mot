<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Vehicle\CreateVehicle\Controller\ClassController;
use Vehicle\CreateVehicle\Controller\ModelController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\EngineForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class EngineAction
{
    private $createVehicleStepService;

    public function __construct(CreateVehicleStepService $createVehicleStepService)
    {
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute(Request $request)
    {
        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::ENGINE_STEP)) {
            return new RedirectToRoute(ModelController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $vehicleData = $this->createVehicleStepService->getStaticData();
        $stepSessionData = $this->createVehicleStepService->getStep(CreateVehicleStepService::ENGINE_STEP);

        $form = new EngineForm($vehicleData['fuelType'], $stepSessionData);

        if ($request->isPost()) {
            $params = $request->getPost()->toArray();
            $form->setData($params);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::ENGINE_STEP, $params);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::ENGINE_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(ClassController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/engine.twig')
            ->setViewModel($viewModel);
    }
}