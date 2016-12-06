<?php

namespace Vehicle\CreateVehicle\Service;

use Application\Service\CatalogService;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Vehicle\CreateVehicle\Service\CreateVehicleSessionService;
use Vehicle\Helper\ColoursContainer;
use DvsaCommon\HttpRestJson\Client;

class CreateVehicleStepService
{
    const NEW_STEP = 'new';
    const REG_VIN_STEP = 'reg-vin';
    const MAKE_STEP = 'make';
    const MODEL_STEP = 'model';
    const ENGINE_STEP = 'engine';
    const CLASS_STEP = 'class';
    const COUNTRY_STEP = 'country';
    const COLOUR_STEP = 'colour';
    const DATE_STEP = 'date';
    const REVIEW_STEP = 'review';
    const CONFIRM_STEP = 'confirm';

    const STEP_VALID = true;
    const STEP_INVALID = false;

    private $createVehicleSessionService;
    private $catalogService;
    private $client;

    public function __construct(CreateVehicleSessionService $sessionService,
                                CatalogService $catalogService,
                                Client $client
    )
    {
        $this->createVehicleSessionService = $sessionService;
        $this->catalogService = $catalogService;
        $this->client = $client;

    }

    public function updateStepStatus($step, $status)
    {
        $sessionStore = $this->createVehicleSessionService->load(CreateVehicleSessionService::UNIQUE_KEY);
        $steps = (!empty($sessionStore[CreateVehicleSessionService::STEP_KEY]))
            ? $sessionStore[CreateVehicleSessionService::STEP_KEY] : [];

        if (empty($steps)) {
            throw new \Exception('Steps are not stored in session');
        }

        if (!isset($steps[$step])) {
            throw new \Exception('Step: ' .$step. ' is not a valid step');
        }

        if (!is_bool($status)) {
            throw new \Exception('Step status must be a boolean');
        }

        $steps[$step] = $status;

        $sessionStore[CreateVehicleSessionService::STEP_KEY] = $steps;

        $this->createVehicleSessionService->save(CreateVehicleSessionService::UNIQUE_KEY, $sessionStore);

    }

    public function getStaticData()
    {
        $sessionStore = $this->createVehicleSessionService->load(CreateVehicleSessionService::UNIQUE_KEY);
        $vehicleData = $sessionStore[CreateVehicleSessionService::API_DATA];

        if (!empty($vehicleData)) {
            return $vehicleData;
        }

        $makes = $this->client->get(UrlBuilder::vehicleDictionary()->make()->toString());
        $catalogData = $this->catalogService->getData();
        $colours = $this->catalogService->getColours();

        $fuelTypes = $this->catalogService->getFuelTypes();

        $vehicleData = [
            'make' => $makes['data'],
            'colour' => $colours,
            'fuelType' => $fuelTypes,
            CreateVehicleStepService::COUNTRY_STEP => $catalogData['countryOfRegistration'],
            'vehicleClass' => $catalogData['vehicleClass'],
        ];

        $sessionStore[CreateVehicleSessionService::API_DATA] = $vehicleData;
        $this->createVehicleSessionService->save(CreateVehicleSessionService::UNIQUE_KEY, $sessionStore);

        return $vehicleData;
    }

    /**
     * @param String $step
     * @param array $stepData
     * @throws \Exception
     */
    public function saveStep($step, array $stepData)
    {
        if (!in_array($step, $this->getSteps())) {
            throw new \Exception("Step $step is not a valid step.");
        }

        $sessionStore = $this->createVehicleSessionService->load(CreateVehicleSessionService::UNIQUE_KEY);
        $stepStore = $sessionStore[CreateVehicleSessionService::USER_DATA];

        $stepStore[$step] = $stepData;

        $sessionStore[CreateVehicleSessionService::USER_DATA] = $stepStore;
        $this->createVehicleSessionService->save(CreateVehicleSessionService::UNIQUE_KEY, $sessionStore);
    }

    /**
     * @param String $step
     * @throws \Exception
     */
    public function getStep($step)
    {
        if (!in_array($step, $this->getSteps())) {
            throw new \Exception("Step $step is not a valid step.");
        }

        $sessionStore = $this->createVehicleSessionService->load(CreateVehicleSessionService::UNIQUE_KEY);
        $stepStore = $sessionStore[CreateVehicleSessionService::USER_DATA];

        return $stepStore[$step];
    }

    /**
     * @param $step
     * @return bool
     */
    public function isAllowedOnStep($step)
    {
        $sessionStore = $this->createVehicleSessionService->load(CreateVehicleSessionService::UNIQUE_KEY);
        $steps = (!empty($sessionStore[CreateVehicleSessionService::STEP_KEY]))
            ? $sessionStore[CreateVehicleSessionService::STEP_KEY] : null;

        // If steps are not loaded return false
        if (is_null($steps) || !is_array($steps)) {
            return false;
        }

        if (!isset($steps[$step])) {
            return false;
        }

        $previousValue = null;

        foreach ($steps as $key => $value) {
            if ($step == $key) {
                return $previousValue;
            }
            $previousValue = $value;
        }
        return false;
    }

    public function loadStepsIntoSession()
    {
        $this->createVehicleSessionService->clear();
        $sessionStore = [];

        $steps = [];

        foreach ($this->getSteps() as $step) {
            $steps[$step] = self::STEP_INVALID;
        }
        $steps[self::NEW_STEP] = self::STEP_VALID;

        $sessionStore[CreateVehicleSessionService::STEP_KEY] = $steps;
        $this->createVehicleSessionService->save(CreateVehicleSessionService::UNIQUE_KEY, $sessionStore);
    }

    /**
     * Returns a list of steps in the journey
     * @return array
     */
    public function getSteps()
    {
        return [
            self::NEW_STEP,
            self::REG_VIN_STEP,
            self::MAKE_STEP,
            self::MODEL_STEP,
            self::ENGINE_STEP,
            self::CLASS_STEP,
            self::COLOUR_STEP,
            self::COUNTRY_STEP,
            self::DATE_STEP,
            self::REVIEW_STEP,
            self::CONFIRM_STEP,
        ];
    }
}