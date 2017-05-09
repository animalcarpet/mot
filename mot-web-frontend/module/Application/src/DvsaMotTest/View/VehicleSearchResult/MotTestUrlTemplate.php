<?php

namespace DvsaMotTest\View\VehicleSearchResult;

use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Model\VehicleSearchResult;
use Zend\Mvc\Controller\Plugin\Url;

class MotTestUrlTemplate implements VehicleSearchResultUrlTemplateInterface
{
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
        $vehicleId = $vehicle['id'];
        $sourceType = $vehicle['source'];
        $vin = $vehicle['vin'];
        $registration = $vehicle['registration'];
        $helper = $this->urlHelper;
        $searchVrm = $vehicle['searchVrm'];
        $searchVin = $vehicle['searchVin'];

        return $helper->fromRoute(
            'start-test-confirmation',
            [
                'controller' => 'StartTestConfirmation',
                'action' => 'index',
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
        $helper = $this->urlHelper;
        $searchVrm = $searchParams['searchVrm'];
        $searchVin = $searchParams['searchVin'];

        if ($vehicle->isRetest()) {
            $retest = true;
        } else {
            $retest = false;
        }

        return $helper->fromRoute(
            'start-test-confirmation',
            [
                'controller' => 'StartTestConfirmation',
                'action' => 'index',
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
                    'retest' => $retest,
                ],
            ]
        );
    }
}
