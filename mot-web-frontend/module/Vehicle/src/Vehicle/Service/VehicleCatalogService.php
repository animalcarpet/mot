<?php

namespace Vehicle\Service;

/**
 * Class VehicleCatalogService.
 *
 * Collect information from the API for list of MAKES and MODELS (associated with a Make Code)
 */
class VehicleCatalogService
{
    const URL_VEHICLE_DICTIONARY = 'vehicle-dictionary';

    /**
     * @var \DvsaCommon\HttpRestJson\Client
     */
    private $restClient;

    /**
     * @param $restClient
     */
    public function __construct($restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param bool $query
     *
     * @return array
     */
    public function findMake($query = false)
    {
        if (!$query) {
            $query = '';
        }

        $params = ['searchType' => 'make', 'searchTerm' => $query];
        $responseFromApi = $this->restClient->getWithParams(self::URL_VEHICLE_DICTIONARY, $params);

        return $this->getResponse($responseFromApi);
    }

    /**
     * @param bool $query
     * @param bool $make
     *
     * @return array
     */
    public function findModel($query = false, $make = false)
    {
        if (!$make) {
            $make = '';
        }

        if (!$query) {
            $query = '';
        }
        $params = ['searchType' => 'model', 'searchTerm' => $query, 'make' => $make];
        $responseFromApi = $this->restClient->getWithParams(self::URL_VEHICLE_DICTIONARY, $params);

        return $this->getResponse($responseFromApi);
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function getResponse($response)
    {
        if (!$response || is_string($response)) {
            return [];
        }

        if (isset($response['data'])) {
            $response = $response['data'];
        }

        return $response;
    }
}
