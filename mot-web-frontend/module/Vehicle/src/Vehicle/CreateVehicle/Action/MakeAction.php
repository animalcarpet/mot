<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Controller\ModelController;
use Vehicle\CreateVehicle\Controller\RegistrationAndVinController;
use Vehicle\CreateVehicle\Form\MakeForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class MakeAction
{
    private $authorisationService;
    private $createVehicleStepService;

    public function __construct(MotAuthorisationServiceInterface $authorisationService,
                                CreateVehicleStepService $createVehicleStepService)
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::MAKE_STEP)) {
            return new RedirectToRoute(RegistrationAndVinController::ROUTE);
        }

        $makes = $this->createVehicleStepService->getStaticData()[CreateVehicleStepService::MAKE_STEP];
        $stepData = $this->createVehicleStepService->getStep(CreateVehicleStepService::MAKE_STEP);

        $form = new MakeForm($makes, $stepData[MakeForm::MODEL], $stepData[MakeForm::OTHER]);

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->createVehicleStepService->saveStep(CreateVehicleStepService::MAKE_STEP, $postData);
                $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::MAKE_STEP, true);

                return new RedirectToRoute(ModelController::ROUTE);
            }
        }

        $viewModel = (new ViewModel())
            ->setVariables(['form' => $form]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/make.twig')
            ->setViewModel($viewModel);
    }
}
