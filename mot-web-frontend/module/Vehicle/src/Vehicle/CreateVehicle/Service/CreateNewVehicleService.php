<?php

namespace Vehicle\CreateVehicle\Service;

use Application\Service\ContingencySessionManager;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\Service\AuthorisedClassesService;
use Vehicle\CreateVehicle\Form\MakeForm;
use Vehicle\CreateVehicle\Form\ModelForm;

class CreateNewVehicleService
{
    /** @var  CreateVehicleStepService */
    private $createVehicleStepService;
    /** @var  VehicleService */
    private $vehicleService;
    /** @var  MotFrontendIdentityProviderInterface */
    private $identityProvider;
    /** @var  ContingencySessionManager */
    private $contingencySessionManager;
    /** @var  Client */
    private $client;
    /** @var AuthorisedClassesService */
    private $authorisedClassesService;

    public function __construct(
        VehicleService $vehicleService,
        CreateVehicleStepService $createVehicleStepService,
        MotFrontendIdentityProviderInterface $identityProvider,
        ContingencySessionManager $contingencySessionManager,
        Client $client,
        AuthorisedClassesService $authorisedClassesService
    )
    {
        $this->createVehicleStepService = $createVehicleStepService;
        $this->vehicleService = $vehicleService;
        $this->identityProvider = $identityProvider;
        $this->contingencySessionManager = $contingencySessionManager;
        $this->client = $client;
        $this->authorisedClassesService = $authorisedClassesService;
    }

    public function getAuthorisedClassesForUserAndVTS()
    {
        $identity = $this->identityProvider->getIdentity();
        $userId = $identity->getUserId();
        $currentVts = $identity->getCurrentVts();

        if (!$currentVts) {
            throw new \Exception("VTS not found");
        }

        $siteId = $currentVts->getVtsId();

        $authorisedClassesCombined = $this->authorisedClassesService->getCombinedAuthorisedClassesForPersonAndVts(
            $userId,
            $siteId
        );

        return $authorisedClassesCombined;
    }

    public function createVehicle()
    {
        $vehicle = $this->vehicleService->createDvsaVehicle(
            $this->prepareCreateVehicleRequest()
        );

        $motTest = $this->createMotTestForVehicle($vehicle);

        $startedMotTestNumber = $motTest['data']['motTestNumber'];

        return [
            'isMotContingency' => $this->contingencySessionManager->isMotContingency(),
            'vehicle' => $vehicle,
            'startedMotTestNumber' => $startedMotTestNumber,
        ];
    }

    private function prepareCreateVehicleRequest()
    {
        $regAndVin = $this->createVehicleStepService->getStep('reg-vin');
        $make = $this->createVehicleStepService->getStep('make');
        $model = $this->createVehicleStepService->getStep('model');
        $engine = $this->createVehicleStepService->getStep('engine');
        $class = $this->createVehicleStepService->getStep('class');
        $country = $this->createVehicleStepService->getStep('country');
        $colour = $this->createVehicleStepService->getStep('colour');
        $date = $this->createVehicleStepService->getStep('date');

        $countryID = $this->getCountryIdFromCountryCode();

        $stepsData = array_merge(
            $regAndVin,
            $make,
            $model,
            $engine,
            $class,
            $colour,
            $country,
            $date
        );

        $OTHER_MAKE_OR_MODEL_ID = -1;
        $makeId = $make['vehicleMake'] === MakeForm::OTHER ? $OTHER_MAKE_OR_MODEL_ID : $stepsData['vehicleMake'];
        $modelId = $model['vehicleModel'] === ModelForm::OTHER ? $OTHER_MAKE_OR_MODEL_ID : $stepsData['vehicleModel'];

        $dateOfFirstUse = [
            $stepsData['dateDay'],
            $stepsData['dateMonth'],
            $stepsData['dateYear'],
        ];

        $createVehicleRequest = new CreateDvsaVehicleRequest();
        $createVehicleRequest
            ->setColourCode($stepsData['primaryColour'])
            ->setCountryOfRegistrationId($countryID)
            ->setFirstUsedDate(new \DateTime(vsprintf('%04d-%02d-%02d',array_reverse($dateOfFirstUse))))
            ->setFuelTypeCode($stepsData['fuel-type'])
            ->setMakeId($makeId)
            ->setModelId($modelId)
            ->setSecondaryColourCode($stepsData['secondaryColours'])
            ->setVehicleClassCode($stepsData['class']);

        if (in_array(
            $stepsData['fuel-type'],
            FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacity())
        ) {
            $createVehicleRequest->setCylinderCapacity($stepsData['cylinder-capacity']);
        }

        if ($makeId === -1) {
            $createVehicleRequest->setMakeOther($make['Other']);
        }

        if ($modelId === -1) {
            $createVehicleRequest->setModelOther($model['Other']);
        }

        if ($stepsData['leavingVINBlank'] == 1) {
            $createVehicleRequest->setEmptyVinReasonId(3);
        } else {
            $createVehicleRequest->setVin($stepsData['vin-input']);
        }

        if ($stepsData['leavingRegBlank'] == 1) {
            $createVehicleRequest->setEmptyVrmReasonId(2);
        } else {
            $createVehicleRequest->setRegistration($stepsData['reg-input']);
        }

        return $createVehicleRequest;
    }

    private function getCountryIdFromCountryCode()
    {
        $countries = $this->createVehicleStepService->getStaticData()[CreateVehicleStepService::COUNTRY_STEP];
        $countryOfRegistration = $this->createVehicleStepService->getStep('country');

        foreach ($countries as $index) {
            foreach ($index as $item) {
                if ($index['code'] === $countryOfRegistration['countryOfRegistration']) {
                    return $item;
                }
            }
        }

        return -1;
    }

    private function prepareNewTestData(DvsaVehicle $vehicle)
    {
        $vehicleTestingStationId = $this->identityProvider->getIdentity()->getCurrentVts()->getVtsId();
        $hasRegistration = is_null($vehicle->getEmptyVrmReason());

        $primaryColour = $vehicle->getColour()->getCode();
        $secondaryColour = $vehicle->getColourSecondary()->getCode();
        $fuelTypeCode = $vehicle->getFuelType()->getCode();

        $data = [
            'vehicleId' => $vehicle->getId(),
            'primaryColour' => $primaryColour,
            'secondaryColour' => $secondaryColour,
            'vehicleClassCode' => $vehicle->getVehicleClass()->getName(),
            'fuelTypeId' => $fuelTypeCode,
            'vehicleTestingStationId' => $vehicleTestingStationId,
            'hasRegistration' => $hasRegistration,
            'motTestType' => MotTestTypeCode::NORMAL_TEST,
        ];

        if ($this->contingencySessionManager->isMotContingency()) {
            $contingencySession = $this->contingencySessionManager->getContingencySession();
            $data += [
                'contingencyId'     => $contingencySession['contingencyId'],
                'contingencyDto'    => DtoHydrator::dtoToJson($contingencySession['dto']),
            ];
        }

        return $data;
    }

    private function createMotTestForVehicle(DvsaVehicle $vehicle)
    {
        $apiUrl = MotTestUrlBuilder::motTest();

        $newTestData = $this->prepareNewTestData($vehicle);

        return $this->client->post($apiUrl, $newTestData);
    }
}