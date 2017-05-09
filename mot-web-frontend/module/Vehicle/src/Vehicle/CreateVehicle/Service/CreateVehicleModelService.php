<?php

namespace Vehicle\CreateVehicle\Service;

use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Vehicle\CreateVehicle\Form\MakeForm;

class CreateVehicleModelService
{
    private $createVehicleStepService;
    private $client;

    public function __construct(CreateVehicleStepService $createVehicleStepService, Client $client)
    {
        $this->createVehicleStepService = $createVehicleStepService;
        $this->client = $client;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getModelFromMakeInSession()
    {
        $makeStepData = $this->createVehicleStepService->getStep(CreateVehicleStepService::MAKE_STEP);
        $makeCode = $makeStepData[MakeForm::MODEL];

        if (empty($makeCode)) {
            throw new \Exception('Make not set in Session');
        }

        if ($makeCode == MakeForm::OTHER) {
            return [];
        }

        $models = $this->client->get(UrlBuilder::vehicleDictionary()->make($makeCode)->models()->toString());

        return isset($models['data']) ? $models['data'] : [];
    }
}
