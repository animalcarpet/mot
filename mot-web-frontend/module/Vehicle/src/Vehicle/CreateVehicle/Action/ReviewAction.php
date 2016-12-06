<?php

namespace Vehicle\CreateVehicle\Action;

use Application\Helper\PrgHelper;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DateTime;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Vehicle\CreateVehicle\Controller\ConfirmationController;
use Vehicle\CreateVehicle\Controller\DateOfFirstUseController;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class ReviewAction
{
    /** @var MotAuthorisationServiceInterface  */
    private $authorisationService;
    /** @var CreateVehicleStepService  */
    private $createVehicleStepService;
    /** @var CreateVehicleModelService  */
    private $createVehicleModelService;
    /** @var CreateNewVehicleService  */
    private $createNewVehicleService;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        CreateVehicleStepService $createVehicleStepService,
        CreateVehicleModelService $createVehicleModelService,
        CreateNewVehicleService $createNewVehicleService
    )
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
        $this->createVehicleModelService = $createVehicleModelService;
        $this->createNewVehicleService = $createNewVehicleService;
    }

    public function execute(Request $request)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::REVIEW_STEP)) {
            return new RedirectToRoute(DateOfFirstUseController::ROUTE);
        }

        $prgHelper = new PrgHelper($request);
        if ($prgHelper->isRepeatPost()) {
            return new RedirectToRoute($prgHelper->getRedirectUrl());
        }

        $regVinStep = $this->createVehicleStepService->getStep('reg-vin');
        $registrationNumber = $regVinStep['reg-input'];
        $vinNumber = $regVinStep['vin-input'];
        $viewModel = (new ViewModel())
            ->setVariables([
                'registrationNumber' => strlen($registrationNumber) > 0 ? $registrationNumber : 'Not provided',
                'vinNumber' => strlen($vinNumber) > 0 ? $vinNumber : 'Not provided',
                'make' => $this->getVehicleMakeNameById(),
                'model' => $this->getVehicleModelNameById(),
                'fuelNameAndEngine' => $this->getFuelNameByType(),
                'class' => $this->createVehicleStepService->getStep('class')['class'],
                'primaryColour' => $this->getPrimaryColourNameById(),
                'secondaryColour' => $this->getSecondaryColourNameById(),
                'country' => $this->getCountryNameFromCountryCode(),
                'date' => $this->getCorrectDateFormat(),
                'prgHelper' => $prgHelper->getHtml(),
            ]);

        if ($request->isPost()) {
            $vehicle = $this->createNewVehicleService->createVehicle();
            $this->createVehicleStepService->saveStep(CreateVehicleStepService::REVIEW_STEP, $vehicle);
            $this->createVehicleStepService->updateStepStatus(CreateVehicleStepService::REVIEW_STEP, true);
            $prgHelper->setRedirectUrl('create-vehicle/new-vehicle-created-and-started');
            return new RedirectToRoute(ConfirmationController::ROUTE);
        }

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/review.twig')
            ->setViewModel($viewModel);
    }

    private function getPrimaryColourNameById()
    {
        $coloursName = '';
        $colour = $this->createVehicleStepService->getStep('colour')['primaryColour'];

        foreach ($this->createVehicleStepService->getStaticData()['colour'] as $id => $name) {
            if ($id == $colour) {
                $coloursName = $name;
            }
        }

        return $coloursName;
    }

    private function getSecondaryColourNameById()
    {
        $coloursName = '';
        $colour = $this->createVehicleStepService->getStep('colour')['secondaryColours'];

        foreach ($this->createVehicleStepService->getStaticData()['colour'] as $id => $name) {
            if ($id == $colour) {
                if ($id != 'W') {
                    $coloursName = $name;
                }
            }
        }

        return $coloursName;
    }

    private function getFuelNameByType()
    {
        $fuelName = '';
        $fuelType = $this->createVehicleStepService->getStep('engine');

        if ($fuelType['fuel-type'] == 'OT') {
            return 'Other, ' . $fuelType['cylinder-capacity'];
        }

        foreach ($this->createVehicleStepService->getStaticData()['fuelType'] as $id => $name) {
            if ($id == $fuelType['fuel-type']) {
                $fuelName = $name . ($fuelType['cylinder-capacity'] ? ', ' . $fuelType['cylinder-capacity'] : '');
            }
        }

        return $fuelName;
    }

    private function getVehicleMakeNameById()
    {
        $makeName = '';
        $vehicleMake = $this->createVehicleStepService->getStep('make')['vehicleMake'];

        if ($vehicleMake == 'Other') {
            return $this->createVehicleStepService->getStep('make')['Other'];
        }

        foreach ($this->createVehicleStepService->getStaticData()['make'] as $makes) {
            if ($makes['id'] == $vehicleMake) {
                $makeName = $makes['name'];
            }
        }

        return $makeName;
    }

    private function getVehicleModelNameById()
    {
        $modelName = '';
        $vehicleModel = $this->createVehicleStepService->getStep('model')['vehicleModel'];

        if ($vehicleModel == 'Other') {
            return $this->createVehicleStepService->getStep('model')['Other'];
        }

        foreach ($this->createVehicleModelService->getModelFromMakeInSession() as $model) {
            if ($model['id'] == $vehicleModel) {
                $modelName = $model['name'];
            }
        }

        return $modelName;
    }

    private function getCountryNameFromCountryCode()
    {
        $countryName = '';
        $countryCode = $this->createVehicleStepService->getStep('country')['countryOfRegistration'];

        foreach ($this->createVehicleStepService->getStaticData()['country'] as $countries) {
            if ($countries['code'] == $countryCode) {
                $countryName = $countries['name'];
            }
        }

        return $countryName;
    }

    private function getCorrectDateFormat()
    {
        $dateFromSession = $this->createVehicleStepService->getStep('date');
        return (new DateTime($dateFromSession['dateMonth'].'/'.$dateFromSession['dateDay'].'/'.$dateFromSession['dateYear']))
            ->format(DateTimeDisplayFormat::FORMAT_DATE);
    }
}