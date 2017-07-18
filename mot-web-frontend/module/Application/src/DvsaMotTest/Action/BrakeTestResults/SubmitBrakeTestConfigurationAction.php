<?php

namespace DvsaMotTest\Action\BrakeTestResults;

use Core\Action\AbstractActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaMotTest\Controller\BrakeTestResultsController;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass1And2Mapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationMapperInterface;
use DvsaMotTest\Service\BrakeTestConfigurationService;

class SubmitBrakeTestConfigurationAction
{
    /** @var WebPerformMotTestAssertion */
    private $webPerformMotTestAssertion;

    /** @var VehicleService */
    private $vehicleService;

    /** @var MotTestService */
    private $motTestService;

    /** @var BrakeTestConfigurationService $brakeTestConfigurationService */
    private $brakeTestConfigurationService;

    /** @var BrakeTestConfigurationClass3AndAboveMapper */
    private $brakeTestConfigurationClass3AndAboveMapper;

    /**
     * @param WebPerformMotTestAssertion                 $webPerformMotTestAssertion
     * @param BrakeTestConfigurationContainerHelper      $brakeTestConfigurationContainerHelper
     * @param VehicleService                             $vehicleService
     * @param MotTestService                             $motTestService
     * @param BrakeTestConfigurationService              $brakeTestConfigurationService
     */
    public function __construct(
        WebPerformMotTestAssertion $webPerformMotTestAssertion,
        BrakeTestConfigurationContainerHelper $brakeTestConfigurationContainerHelper,
        VehicleService $vehicleService,
        MotTestService $motTestService,
        BrakeTestConfigurationService $brakeTestConfigurationService,
        BrakeTestConfigurationClass3AndAboveMapper $brakeTestConfigurationClass3AndAboveMapper
    ) {
        $this->webPerformMotTestAssertion = $webPerformMotTestAssertion;
        $this->brakeTestConfigurationContainerHelper = $brakeTestConfigurationContainerHelper;
        $this->vehicleService = $vehicleService;
        $this->motTestService = $motTestService;
        $this->brakeTestConfigurationService = $brakeTestConfigurationService;
        $this->brakeTestConfigurationClass3AndAboveMapper = $brakeTestConfigurationClass3AndAboveMapper;
    }

    /**
     * @param array $requestData
     * @param int   $motTestNumber
     *
     * @return AbstractActionResult
     */
    public function execute(array $requestData, $motTestNumber)
    {
        $actionResult = new ViewActionResult();
        $motTest = $this->getMotTest($motTestNumber, $actionResult);

        $this->webPerformMotTestAssertion->assertGranted($motTest);

        if ($motTest->getStatus() !== MotTestStatusName::ACTIVE) {
            return $this->redirectToMotTestWithInvalidTestStatusError($motTest);
        }

        $vehicle = $this->getVehicleFromTest($motTest);

        $configDtoMapper = $this->getBrakeTestMapperService($vehicle);
        $dto = $configDtoMapper->mapToDto($requestData);

        try {
            $this->brakeTestConfigurationService->validateConfiguration($dto, $motTestNumber);

            return $this->redirectToAddBrakeTestResult($requestData, $motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($actionResult, $e->getDisplayMessages());
        }

        return $actionResult;
    }

    /**
     * @param MotTest $motTest
     *
     * @return DvsaVehicle
     */
    private function getVehicleFromTest(MotTest $motTest)
    {
        return $this->vehicleService->getDvsaVehicleByIdAndVersion(
            $motTest->getVehicleId(),
            $motTest->getVehicleVersion()
        );
    }

    /**
     * @param int                  $motTestNumber
     * @param AbstractActionResult $actionResult
     *
     * @return MotTest
     */
    private function getMotTest($motTestNumber, $actionResult)
    {
        try {
            return $this->motTestService->getMotTestByTestNumber($motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($actionResult, $e->getDisplayMessages());

            return null;
        }
    }

    /**
     * @param DvsaVehicle $vehicle
     *
     * @return BrakeTestConfigurationMapperInterface
     */
    private function getBrakeTestMapperService($vehicle)
    {
        $vehicleClass = $vehicle->getVehicleClass()->getCode();
        $isGroupA = VehicleClassGroup::isGroupA($vehicleClass);

        if ($isGroupA) {
            return new BrakeTestConfigurationClass1And2Mapper();
        } else {
            return $this->brakeTestConfigurationClass3AndAboveMapper;
        }
    }

    /**
     * @param array $brakeTestConfiguration
     * @param $motTestNumber
     *
     * @return RedirectToRoute
     */
    private function redirectToAddBrakeTestResult(array $brakeTestConfiguration, $motTestNumber)
    {
        $brakeTestConfigurationContainer = $this->brakeTestConfigurationContainerHelper;
        $brakeTestConfigurationContainer->persistConfig($brakeTestConfiguration);

        return new RedirectToRoute(
            BrakeTestResultsController::ROUTE_MOT_TEST_BRAKE_TEST_RESULTS,
            ['motTestNumber' => $motTestNumber]
        );
    }

    /**
     * @param MotTest $motTest
     *
     * @return RedirectToRoute
     */
    private function redirectToMotTestWithInvalidTestStatusError(MotTest $motTest)
    {
        $redirect = new RedirectToRoute(MotTestController::ROUTE_MOT_TEST, ['motTestNumber' => $motTest->getMotTestNumber()]);
        $this->addErrorMessages($redirect, InvalidTestStatus::getMessage($motTest->getStatus()));

        return $redirect;
    }

    /**
     * @param AbstractActionResult $actionResult
     * @param $messages
     *
     * @return $this
     */
    private function addErrorMessages(AbstractActionResult $actionResult, $messages)
    {
        if (is_array($messages)) {
            $actionResult->addErrorMessages($messages);
        } else {
            $actionResult->addErrorMessage($messages);
        }

        return $this;
    }
}
