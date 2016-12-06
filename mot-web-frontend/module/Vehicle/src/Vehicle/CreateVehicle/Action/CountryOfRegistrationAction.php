<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Controller\ColourController;
use Vehicle\CreateVehicle\Controller\DateOfFirstUseController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\CountryOfRegistrationForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class CountryOfRegistrationAction
{
    private $createVehicleStepService;
    private $authorisationService;

    public function __construct(CreateVehicleStepService $createVehicleStepService, MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);
        
        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::COUNTRY_STEP)) {
            return new RedirectToRoute(ColourController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);

        $countries = $this->createVehicleStepService->getStaticData()[CreateVehicleStepService::COUNTRY_STEP];
        $sessionData = $this->createVehicleStepService->getStep(CreateVehicleStepService::COUNTRY_STEP);

        $form = new CountryOfRegistrationForm($countries, $sessionData[CountryOfRegistrationForm::COUNTRY_OF_REGISTRATION_NAME]);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);
            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::COUNTRY_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::COUNTRY_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(DateOfFirstUseController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/countryOfRegistration.twig')
            ->setViewModel($viewModel);
    }
}