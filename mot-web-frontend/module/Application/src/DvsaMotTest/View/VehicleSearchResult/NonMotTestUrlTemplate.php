<?php

namespace DvsaMotTest\View\VehicleSearchResult;

use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Model\VehicleSearchResult;
use Zend\Mvc\Controller\Plugin\Url;

class NonMotTestUrlTemplate implements VehicleSearchResultUrlTemplateInterface
{
    const START_NON_MOT_TEST_CONFIRMATION_ROUTE = 'start-non-mot-test-confirmation';
    const START_TEST_CONFIRMATION_CONTROLLER = 'StartTestConfirmation';
    const NOT_MOT_TEST_ACTION = 'notMotTest';

    /**
     * @var int
     */
    private $noRegistration;
    /**
     * @var Url
     */
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
        $vehicleId = ArrayUtils::get($vehicle, 'id');
        $sourceType = ArrayUtils::get($vehicle, 'source');
        $vin = ArrayUtils::get($vehicle, 'vin');
        $registration = ArrayUtils::get($vehicle, 'registration');
        $searchVrm = ArrayUtils::get($vehicle, 'searchVrm');
        $searchVin = ArrayUtils::get($vehicle, 'searchVin');

        return $this->urlHelper->fromRoute(
            self::START_NON_MOT_TEST_CONFIRMATION_ROUTE,
            [
                'controller' => self::START_TEST_CONFIRMATION_CONTROLLER,
                'action' => self::NOT_MOT_TEST_ACTION,
                StartTestConfirmationController::ROUTE_PARAM_ID => $vehicleId,
                StartTestConfirmationController::ROUTE_PARAM_NO_REG => $this->noRegistration,
                StartTestConfirmationController::ROUTE_PARAM_SOURCE => $sourceType,

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
        $vehicleId = $vehicle->getId();
        $sourceType = $vehicle->getSource();
        $vin = $vehicle->getVin();
        $registration = $vehicle->getRegistrationNumber();
        $searchVrm = $searchParams['searchVrm'];
        $searchVin = $searchParams['searchVin'];

        return $this->urlHelper->fromRoute(
            self::START_NON_MOT_TEST_CONFIRMATION_ROUTE,
            [
                'controller' => self::START_TEST_CONFIRMATION_CONTROLLER,
                'action' => self::NOT_MOT_TEST_ACTION,
                StartTestConfirmationController::ROUTE_PARAM_ID => $vehicleId,
                StartTestConfirmationController::ROUTE_PARAM_NO_REG => $this->noRegistration,
                StartTestConfirmationController::ROUTE_PARAM_SOURCE => $sourceType,
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
}
