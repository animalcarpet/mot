<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Vehicle\CreateVehicle\Controller\ColourController;
use Vehicle\CreateVehicle\Controller\EngineController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\ClassForm;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;

class ClassAction
{
    const CREATE_VEHICLE_CLASS_TEMPLATE = 'vehicle/create-vehicle/class.twig';

    /* @var MotAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /* @var CreateVehicleStepService $createVehicleStepService */
    private $createVehicleStepService;

    /** @var CreateNewVehicleService */
    private $createNewVehicleService;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        CreateVehicleStepService $createVehicleStepService,
        CreateNewVehicleService $createNewVehicleService
    ) {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
        $this->createNewVehicleService = $createNewVehicleService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::CLASS_STEP)) {
            return new RedirectToRoute(EngineController::ROUTE);
        }

        $isAllowedOnReview = $this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP);
        $allowedClasses = $this->createNewVehicleService->getAuthorisedClassesForUserAndVTS();
        $sessionData = $this->createVehicleStepService->getStep(CreateVehicleStepService::CLASS_STEP);

        $form = new ClassForm($sessionData[ClassForm::FIELD_CLASS], $allowedClasses);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);
            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::CLASS_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::CLASS_STEP, true);

                if ($isAllowedOnReview) {
                    return new RedirectToRoute(ReviewController::ROUTE);
                }

                return new RedirectToRoute(ColourController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables([
                'form' => $form,
                'continueButtonLabel' => $isAllowedOnReview ? 'Save and return to review' : 'Continue',
            ]);

        return (new ViewActionResult())
            ->setTemplate(self::CREATE_VEHICLE_CLASS_TEMPLATE)
            ->setViewModel($viewModel);
    }
}
