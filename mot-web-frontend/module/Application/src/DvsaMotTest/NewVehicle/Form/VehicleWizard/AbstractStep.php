<?php
namespace DvsaMotTest\NewVehicle\Form\VehicleWizard;

use DvsaMotTest\NewVehicle\Container\NewVehicleContainer;
use DvsaCommon\HttpRestJson\Client;
use Application\Service\CatalogService;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Vehicle\Helper\ColoursContainer;
use Zend\Form\Form;

abstract class AbstractStep
{
    const API_DATA = "api_data";

    /**  @var NewVehicleContainer */
    protected $container;

    /** @var  Client */
    protected $client;

    /** @var  CatalogService */
    protected $catalogService;

    /** @var  WizardStep */
    protected $prevStep;

    /**
     * @param NewVehicleContainer $container
     * @param Client $client
     * @param CatalogService $catalogService
     */
    public function __construct(
        NewVehicleContainer $container,
        Client $client,
        CatalogService $catalogService
    )
    {
        $this->container = $container;
        $this->client = $client;
        $this->catalogService = $catalogService;
    }

    /**
     * @return array
     */
    protected function getStaticData()
    {
        $vehicleData = $this->container->get(self::API_DATA);

        if (!empty($vehicleData)) {
            return $vehicleData;
        }

        $makes = $this->client->get(UrlBuilder::vehicleDictionary()->make()->toString());
        $catalogData = $this->catalogService->getData();
        $colours = (new ColoursContainer($this->catalogService->getColoursWithIds(), true, true));

        $fuelTypes = [];
        foreach ($this->catalogService->getFuelTypesWithId() as $id => $name) {
            $fuelTypes[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        $vehicleData = [
            'make' => $makes['data'],
            'colour' => $colours->getPrimaryColours(),
            'secondaryColour' => $colours->getSecondaryColours(),
            'fuelType' => $fuelTypes,
            'countryOfRegistration' => $catalogData['countryOfRegistration'],
            'transmissionType' => array_reverse($catalogData['transmissionType'], true),
            'vehicleClass' => $catalogData['vehicleClass'],
            'model' => [],
            'emptyVrmReasons' => $catalogData['reasonsForEmptyVRM'],
            'emptyVinReasons' => $catalogData['reasonsForEmptyVIN'],
        ];

        $this->saveStaticData($vehicleData);

        return $vehicleData;
    }

    /**
     * @param array $data
     */
    protected function saveStaticData(array $data)
    {
        $this->container->set(self::API_DATA, $data);
    }

    /**
     * @param Form $form
     *
     * @return array|object
     */
    protected function getFormData(Form $form)
    {
        if (!$form->hasValidated()) {
            //form cannot return data as validation has not yet occurred
            $form->isValid();
        }

        return $form->getData();
    }

    /**
     * @param WizardStep $step
     */
    public function setPrevStep(WizardStep $step)
    {
        $this->prevStep = $step;
    }

    /**
     * @return WizardStep
     */
    public function getPrevStep()
    {
        return $this->prevStep;
    }
}
