<?php

namespace DvsaMotTest\View\VehicleSearchResult;

use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Model\VehicleSearchResult;
use Zend\Mvc\Controller\Plugin\Url;

class TrainingTestUrlTemplate implements VehicleSearchResultUrlTemplateInterface
{
    const TRAINING_TEST_CONFIRMATION_ROUTE = 'start-training-test-confirmation';

    /** @var int */
    private $noRegistration;

    /** @var Url */
    private $urlHelper;

    /**
     * @param int $noRegistration
     * @param Url $urlPlugin
     */
    public function __construct($noRegistration, Url $urlPlugin)
    {
        $this->noRegistration = $noRegistration;
        $this->urlHelper = $urlPlugin;
    }

    /**
     * @param array $vehicle
     *
     * @return string
     */
    public function getUrl(array $vehicle)
    {
        $this->validateVehicleArray($vehicle, ['id', 'searchVrm', 'searchVin']);

        $vehicleId = $vehicle['id'];
        $vin = $vehicle['vin'];
        $registration = $vehicle['registration'];
        $searchVrm = $vehicle['searchVrm'];
        $searchVin = $vehicle['searchVin'];

        $helper = $this->urlHelper;

        return $helper->fromRoute(
            self::TRAINING_TEST_CONFIRMATION_ROUTE,
            [
                StartTestConfirmationController::ROUTE_PARAM_ID => $vehicleId,
                StartTestConfirmationController::ROUTE_PARAM_NO_REG => $this->noRegistration,
            ],
            [
                'query' => [
                    'vin' => $vin,
                    'registration' => $registration,
                    'searchVrm' => $searchVrm,
                    'searchVin' => $searchVin,
                ],
            ]
        );
    }

    /**
     * @param VehicleSearchResult $vehicle
     * @param array               $searchParams
     *
     * @return string
     */
    public function getStartMotTestUrl(VehicleSearchResult $vehicle, array $searchParams)
    {
        if (!$vehicle) {
            throw new \InvalidArgumentException('Expecting a Vehicle object');
        }

        $this->validateVehicleArray($searchParams, ['searchVrm', 'searchVin']);

        $vehicleId = $vehicle->getId();
        $vin = $vehicle->getVin();
        $registration = $vehicle->getRegistrationNumber();

        $searchVrm = $searchParams['searchVrm'];
        $searchVin = $searchParams['searchVin'];

        $helper = $this->urlHelper;

        return $helper->fromRoute(
            self::TRAINING_TEST_CONFIRMATION_ROUTE,
            [
                StartTestConfirmationController::ROUTE_PARAM_ID => $vehicleId,
                StartTestConfirmationController::ROUTE_PARAM_NO_REG => $this->noRegistration,
            ],
            [
                'query' => [
                    'vin' => $vin,
                    'registration' => $registration,
                    'searchVrm' => $searchVrm,
                    'searchVin' => $searchVin,
                ],
            ]
        );
    }

    /**
     * @param array $vehicle
     * @param array $keys
     */
    private function validateVehicleArray(array $vehicle, array $keys)
    {
        if (empty($vehicle)) {
            throw new \InvalidArgumentException('Vehicle is required');
        }

        $vehicleKeyComparison = array_diff($keys, array_keys($vehicle));

        if (count($vehicleKeyComparison) > 0) {
            throw new \InvalidArgumentException('Vehicle ID and/or Search parameters are missing');
        }
    }
}
