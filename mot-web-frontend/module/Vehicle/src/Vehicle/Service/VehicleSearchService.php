<?php

namespace Vehicle\Service;

use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SearchVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\VehicleUrlBuilderWeb;
use Vehicle\Controller\VehicleController;
use Zend\View\Model\ViewModel;

/**
 * Class VehicleSearchService.
 *
 * This class is used to decorate the data from the outcome of a vehicle search.
 */
class VehicleSearchService
{
    const NO_RESULT_FOUND       = 'Search term(s) not found...';
    const VEHICLE_SEARCH_TERM   = 'search';
    const VEHICLE_TYPE_TERM     = 'type';

    /* @var \Vehicle\Controller\VehicleController */
    protected $controller;
    /* @var \DvsaCommon\HttpRestJson\Client */
    protected $restClient;
    /** @var  Collection */
    protected $apiResults;
    protected $postData   = [];
    protected $searchData = [];
    protected $paramObfuscator;

    /**
     * @param $controller
     * @param $restClient
     * @param $data
     * @param \DvsaCommon\Obfuscate\ParamObfuscator $paramObfuscator
     */
    public function __construct($controller, $restClient, $data, ParamObfuscator $paramObfuscator)
    {
        $this->controller      = $controller;
        $this->restClient      = $restClient;
        $this->paramObfuscator = $paramObfuscator;

        $this->postData = [
            self::VEHICLE_SEARCH_TERM => $data[self::VEHICLE_SEARCH_TERM],
            self::VEHICLE_TYPE_TERM   => $data[self::VEHICLE_TYPE_TERM],
            'format'                  => 'DATA_TABLES',
            'rowCount'                => 500,
        ];

        $this->searchData = [
            'type'   => $data[self::VEHICLE_TYPE_TERM],
            'search' => $data[self::VEHICLE_SEARCH_TERM],
        ];
    }

    /**
     * @return Collection
     */
    public function getVehicleResults()
    {
        $vehicleService = $this->controller->getServiceLocator()->get(VehicleService::class);

        if ($this->searchData['type'] == 'registration') {
            $vehicleCollection = $vehicleService->search($this->searchData['search'], null);
        } else {
            $vehicleCollection =  $vehicleService->search(null, $this->searchData['search']);
        }

        $this->apiResults = $vehicleCollection;

        return $this->apiResults;
    }

    public function checkVehicleResults()
    {
        $data       = $this->apiResults;
        $totalCount = $data->getCount();

        if ($totalCount == 0) {
            $this->controller->addErrorMessagesFromService(self::NO_RESULT_FOUND);

            return $this->controller->redirect()->toUrl(
                VehicleUrlBuilderWeb::search()->queryParams($this->searchData)
            );
        } elseif ($totalCount == 1) {
            /** @var SearchVehicle $vehicle */
            $vehicle = $data->getItem(0);
            return $this->controller->redirect()->toRoute(
                "vehicle/detail",
                [
                    "id" => $this->paramObfuscator->obfuscate($vehicle->getId()),
                ],
                [
                    "query" => [
                        "backTo" => VehicleController::BACK_TO_SEARCH,
                        "type" => $this->postData[VehicleSearchService::VEHICLE_TYPE_TERM],
                        "search" => $this->postData[VehicleSearchService::VEHICLE_SEARCH_TERM],
                    ]
                ]
            );
        }

        return new ViewModel(
            [
                'vehicles'         => $data,
                'resultCount'      => $totalCount,
                'totalResultCount' => $totalCount,
                'search'           => $this->postData['search'],
                'type'             => $this->postData['type'],
                'searchData'       => $this->searchData + ['backTo' => VehicleController::BACK_TO_RESULT],
                'paramObfuscator'  => $this->paramObfuscator,
            ]
        );
    }
}
