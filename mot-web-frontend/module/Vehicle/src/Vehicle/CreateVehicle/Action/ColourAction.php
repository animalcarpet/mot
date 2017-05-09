<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Controller\ClassController;
use Vehicle\CreateVehicle\Controller\CountryOfRegistrationController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\ColourForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class ColourAction
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

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::COLOUR_STEP)) {
            return new RedirectToRoute(ClassController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $staticData = $this->createVehicleStepService->getStaticData();
        $sessionData = $this->createVehicleStepService->getStep('colour');

        $form = new ColourForm($staticData['colour'], $sessionData['primaryColour'], $sessionData['secondaryColours']);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::COLOUR_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::COLOUR_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(CountryOfRegistrationController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/colour.twig')
            ->setViewModel($viewModel);
    }
}
