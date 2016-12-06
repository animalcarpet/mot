<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Controller\EngineController;
use Vehicle\CreateVehicle\Controller\MakeController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\ModelForm;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class ModelAction
{
    private $authorisationService;
    private $createVehicleStepService;
    private $createVehicleModelService;

    public function __construct(MotAuthorisationServiceInterface $authorisationService,
                                CreateVehicleStepService $createVehicleStepService,
                                CreateVehicleModelService $createVehicleModelService
    )
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
        $this->createVehicleModelService = $createVehicleModelService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::MODEL_STEP)) {
            return new RedirectToRoute(MakeController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $stepData = $this->createVehicleStepService->getStep(CreateVehicleStepService::MODEL_STEP);
        $models = $this->createVehicleModelService->getModelFromMakeInSession();
        $form = new ModelForm($models, $stepData[ModelForm::MODEL], $stepData[ModelForm::OTHER]);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::MODEL_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::MODEL_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(EngineController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/model.twig')
            ->setViewModel($viewModel);
    }
}