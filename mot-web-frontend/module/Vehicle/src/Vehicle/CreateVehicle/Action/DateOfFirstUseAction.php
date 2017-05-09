<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Controller\CountryOfRegistrationController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\DateOfFirstUseForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class DateOfFirstUseAction
{
    private $authorisationService;
    private $createVehicleStepService;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        CreateVehicleStepService $createVehicleStepService
    ) {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::DATE_STEP)) {
            return new RedirectToRoute(CountryOfRegistrationController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $stepSessionData = $this->createVehicleStepService->getStep(CreateVehicleStepService::DATE_STEP);
        $form = new DateOfFirstUseForm($stepSessionData);

        if ($request->isPost()) {
            $params = $request->getPost()->toArray();
            $form->setData($params);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::DATE_STEP, $params);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::DATE_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(ReviewController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/dateOfFirstUse.twig')
            ->setViewModel($viewModel);
    }
}
