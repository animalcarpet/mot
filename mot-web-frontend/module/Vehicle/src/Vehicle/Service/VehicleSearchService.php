<?php

namespace Vehicle\Service;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
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

    public function getVehicleResults()
    {
        $vehicleService = $this->controller->getServiceLocator()->get(VehicleService::class);

        if ($this->searchData['type'] == 'registration') {
            $vehicleCollection = $vehicleService->internalSearch($this->searchData['search'], null);
        } else {
            $vehicleCollection =  $vehicleService->internalSearch(null, $this->searchData['search']);
        }

        $this->apiResults = $vehicleCollection;
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
