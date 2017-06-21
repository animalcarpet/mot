<?php

namespace DvsaMotTest\Action\BrakeTestResults;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Core\Action\AbstractActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass1And2;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass1And2Mapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationMapperInterface;
use DvsaMotTest\Model\BrakeTestConfigurationClass1And2Helper;
use DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper;
use DvsaMotTest\Model\BrakeTestConfigurationHelperInterface;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use DvsaMotTest\View\Model\MotTestTitleModel;
use Zend\View\Model\ViewModel;

class ViewBrakeTestConfigurationAction
{
    const VEHICLE_TYPE_CHECK_START_DATE = '2010-09-01';
    const TEMPLATE_CONFIG_CLASS_1_2 = 'dvsa-mot-test/brake-test-results/brake-test-configuration-class12';
    const TEMPLATE_CONFIG_CLASS_3_AND_ABOVE = 'dvsa-mot-test/brake-test-results/brake-test-configuration-class3-and-above';
    const PAGE_TITLE__BRAKE_TEST_CONFIGURATION = 'Brake test configuration';
    const VEHICLE_WEIGHT_TYPE__BRAKE_TEST_WEIGHT = 'Brake test weight (from manufacturer or other reliable data)';
    const VEHICLE_WEIGHT_TYPE__PRESENTED_WEIGHT = 'Presented weight (measured during ATL brake test)';
    const VEHICLE_WEIGHT_TYPE__NOT_KNOWN = 'Not known';
    const VEHICLE_WEIGHT_TYPE__DGW_MAM = 'DGW (design gross weight from manufacturers plate) or MAM (maximum authorised mass)';
    const VEHICLE_WEIGHT_TYPE__CALCULATED_WEIGHT = 'Calculated weight (see the MOT inspection manual for the calculation)';
    const VEHICLE_WEIGHT_TYPE__DGW = 'DGW (design gross weight from manufacturers plate)';

    /** @var WebPerformMotTestAssertion */
    private $webPerformMotTestAssertion;

    /** @var ContingencySessionManager */
    private $contingencySessionManager;

    /** @var CatalogService */
    private $catalogService;

    /** @var Client */
    private $restClient;

    /** @var VehicleService */
    private $vehicleService;

    /** @var MotTestService */
    private $motTestService;

    /** @var AbstractActionResult */
    private $previousActionResult;

    /** @var array */
    private $previousData = [];

    /**
     * ConfigureBrakeTestAction constructor.
     *
     * @param WebPerformMotTestAssertion            $webPerformMotTestAssertion
     * @param ContingencySessionManager             $contingencySessionManager
     * @param CatalogService                        $catalogService
     * @param Client                                $restClient
     * @param BrakeTestConfigurationContainerHelper $brakeTestConfigurationContainerHelper
     * @param VehicleService                        $vehicleService
     * @param MotTestService                        $motTestService
     * @param BrakeTestConfigurationService         $brakeTestConfigurationService
     */
    public function __construct(
        WebPerformMotTestAssertion $webPerformMotTestAssertion,
        ContingencySessionManager $contingencySessionManager,
        CatalogService $catalogService,
        Client $restClient,
        BrakeTestConfigurationContainerHelper $brakeTestConfigurationContainerHelper,
        VehicleService $vehicleService,
        MotTestService $motTestService,
        BrakeTestConfigurationService $brakeTestConfigurationService
    ) {
        $this->webPerformMotTestAssertion = $webPerformMotTestAssertion;
        $this->contingencySessionManager = $contingencySessionManager;
        $this->catalogService = $catalogService;
        $this->restClient = $restClient;
        $this->brakeTestConfigurationContainerHelper = $brakeTestConfigurationContainerHelper;
        $this->vehicleService = $vehicleService;
        $this->motTestService = $motTestService;
        $this->brakeTestConfigurationService = $brakeTestConfigurationService;
    }

    /**
     * @param AbstractActionResult $previousActionResult
     * @param array                $data
     *
     * @return $this
     */
    public function setPreviousActionResult(AbstractActionResult $previousActionResult, array $data = [])
    {
        $this->previousActionResult = $previousActionResult;
        $this->previousData = $data;

        return $this;
    }

    /**
     * @param int $motTestNumber
     *
     * @return RedirectToRoute|ViewActionResult
     */
    public function execute($motTestNumber)
    {
        $actionResult = new ViewActionResult();
        $motTest = $this->getMotTest($motTestNumber, $actionResult);

        $this->webPerformMotTestAssertion->assertGranted($motTest);

        if ($motTest->getStatus() !== MotTestStatusName::ACTIVE) {
            return $this->redirectToMotTestWithInvalidTestStatusError($motTest);
        }

        $vehicle = $this->getVehicleFromVehicleService($motTest);
        $isGroupA = VehicleClassGroup::isGroupA($vehicle->getVehicleClass()->getCode());
        $brakeTestResult = $this->getBrakeTestResultFromMotTest($motTest, $isGroupA);
        $configDtoMapper = $this->getBrakeTestMapperService($isGroupA);

        if ($this->hasPreviousActionResult()) {
            $dto = $configDtoMapper->mapToDto($this->previousData);
            $this->addErrorMessages($actionResult, $this->previousActionResult->getErrorMessages());
        } else {
            $dto = $this->createDtoFromMotTest($motTest, $configDtoMapper, $vehicle->getVehicleClass()->getCode());

            if (!$isGroupA && $brakeTestResult) {
                $dto = $this->updateDtoWithGroupBBrakeTestResult($dto, $brakeTestResult);
            }
        }

        $brakeTestConfigurationViewModel = $isGroupA ?
            $this->buildViewModelForGroupAVehicle($vehicle, $motTest, $dto, $brakeTestResult) :
            $this->buildViewModelForGroupBVehicle($vehicle, $motTest, $dto);

        $actionResult->setViewModel($brakeTestConfigurationViewModel);
        $actionResult->setTemplate($brakeTestConfigurationViewModel->getTemplate());

        return $actionResult;
    }

    /**
     * @param DvsaVehicle                         $vehicle
     * @param MotTest                             $motTest
     * @param BrakeTestConfigurationClass1And2Dto $dto
     * @param BrakeTestResultClass1And2           $brakeTestResult
     *
     * @return ViewModel
     */
    private function buildViewModelForGroupAVehicle(
        DvsaVehicle $vehicle,
        MotTest $motTest,
        BrakeTestConfigurationClass1And2Dto $dto,
        BrakeTestResultClass1And2 $brakeTestResult
    ) {
        $brakeTestConfigurationViewModel = new ViewModel();

        $configHelper = new BrakeTestConfigurationClass1And2Helper($dto);

        $this->setViewModelDataForBothClasses($brakeTestConfigurationViewModel, $motTest, $vehicle);
        $this->setViewModelDataForGroupA($brakeTestConfigurationViewModel, $brakeTestResult, $configHelper);
        $brakeTestConfigurationViewModel->setTemplate(self::TEMPLATE_CONFIG_CLASS_1_2);

        return $brakeTestConfigurationViewModel;
    }

    /**
     * @param DvsaVehicle                             $vehicle
     * @param MotTest                                 $motTest
     * @param BrakeTestConfigurationClass3AndAboveDto $dto
     *
     * @return ViewModel
     */
    private function buildViewModelForGroupBVehicle(
        DvsaVehicle $vehicle,
        MotTest $motTest,
        BrakeTestConfigurationClass3AndAboveDto $dto
    ) {
        $brakeTestConfigurationViewModel = new ViewModel();

        $configHelper = new BrakeTestConfigurationClass3AndAboveHelper($dto);

        $hasCorrectServiceBrakeTestType =
            in_array($configHelper->getServiceBrakeTestType(), [BrakeTestTypeCode::PLATE, BrakeTestTypeCode::ROLLER]);
        $hasCorrectParkingBrakeTestType =
            in_array($configHelper->getParkingBrakeTestType(), [BrakeTestTypeCode::PLATE, BrakeTestTypeCode::ROLLER]);

        $preselectBrakeTestWeight = (
            ($configHelper->getVehicleWeight() || $motTest->getPreviousTestVehicleWight()) &&
            ($hasCorrectServiceBrakeTestType || $hasCorrectParkingBrakeTestType)
        );

        $this->setViewModelDataForBothClasses($brakeTestConfigurationViewModel, $motTest, $vehicle);
        $this->setViewModelDataForGroupB($brakeTestConfigurationViewModel, $vehicle, $configHelper, $preselectBrakeTestWeight);
        $brakeTestConfigurationViewModel->setTemplate(self::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE);

        return $brakeTestConfigurationViewModel;
    }

    /**
     * @param $brakeTestConfigurationViewModel
     * @param $motTest
     * @param $vehicle
     *
     * @return ViewModel
     */
    private function setViewModelDataForBothClasses($brakeTestConfigurationViewModel, $motTest, $vehicle)
    {
        return $brakeTestConfigurationViewModel->setVariables([
            'isMotContingency' => $this->contingencySessionManager->isMotContingency(),
            'brakeTestTypes' => array_reverse($this->catalogService->getBrakeTestTypes()),
            'motTest' => $motTest,
            'motTestTitleViewModel' => (new MotTestTitleModel()),
            'vehicle' => $vehicle,
            'vehicleClass' => $vehicle->getVehicleClass()->getCode(),
            'title' => self::PAGE_TITLE__BRAKE_TEST_CONFIGURATION,
            'showVehicleType' => $this->displayConfigurationVehicleType($vehicle),
        ]);
    }

    /**
     * @param BrakeTestConfigurationClass3AndAboveDto $dto
     * @param BrakeTestResultClass3AndAbove           $brakeTestResult
     *
     * @return BrakeTestConfigurationClass3AndAboveDto
     */
    private function updateDtoWithGroupBBrakeTestResult(
        BrakeTestConfigurationClass3AndAboveDto $dto,
        BrakeTestResultClass3AndAbove $brakeTestResult
    ) {
        $dto->setServiceBrakeIsSingleLine($brakeTestResult->getServiceBrakeIsSingleLine());
        $dto->setNumberOfAxles($brakeTestResult->getNumberOfAxles());
        $dto->setParkingBrakeNumberOfAxles($brakeTestResult->getParkingBrakeNumberOfAxles());
        $dto->setWeightType($brakeTestResult->getWeightType());
        $dto->setServiceBrake1TestType($brakeTestResult->getServiceBrake1TestType());
        $dto->setServiceBrake2TestType($brakeTestResult->getServiceBrake2TestType());
        $dto->setParkingBrakeTestType($brakeTestResult->getParkingBrakeTestType());
        $dto->setVehicleWeight($brakeTestResult->getVehicleWeight());
        $dto->setWeightIsUnladen($brakeTestResult->getWeightIsUnladen());
        $dto->setIsCommercialVehicle($brakeTestResult->getCommercialVehicle());
        $dto->setIsSingleInFront($brakeTestResult->getSingleInFront());

        return $dto;
    }

    /**
     * @param MotTest                               $motTest
     * @param BrakeTestConfigurationMapperInterface $configDtoMapper
     * @param string                                $vehicleClassCode
     *
     * @return BrakeTestConfigurationClass1And2Dto|BrakeTestConfigurationClass3AndAboveDto
     */
    private function createDtoFromMotTest(MotTest $motTest, BrakeTestConfigurationMapperInterface $configDtoMapper, $vehicleClassCode)
    {
        /** @var BrakeTestConfigurationClass3AndAboveDto | BrakeTestConfigurationClass1And2Dto $dto */
        $dto = $configDtoMapper->mapToDefaultDto($motTest);

        if ($this->canOverwriteWithVtsDefaultSettings($motTest)) {
            // Overwrite the brakeTestTypes with defaults from site info
            $this->populateBrakeTestTypeWithSiteDefaults($dto, $motTest, $vehicleClassCode);
        }

        return $dto;
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
     * @return bool
     */
    private function hasPreviousActionResult()
    {
        return $this->previousActionResult !== null;
    }

    /**
     * @param MotTest $motTest
     * @param bool    $isGroupA
     *
     * @return BrakeTestResultClass1And2|BrakeTestResultClass3AndAbove|null
     */
    private function getBrakeTestResultFromMotTest(MotTest $motTest, $isGroupA)
    {
        $brakeTestResult = null;

        $brakeTestResultData = $motTest->getBrakeTestResult();

        if ($brakeTestResultData !== null) {
            if ($isGroupA) {
                $brakeTestResult = new BrakeTestResultClass1And2($brakeTestResultData);
            } else {
                $brakeTestResult = new BrakeTestResultClass3AndAbove($brakeTestResultData);
            }
        }

        return $brakeTestResult;
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
     * @param $motTest
     *
     * @return DvsaVehicle
     */
    private function getVehicleFromVehicleService($motTest)
    {
        return $this->vehicleService->getDvsaVehicleByIdAndVersion(
            $motTest->getVehicleId(),
            $motTest->getVehicleVersion()
        );
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    private function canOverwriteWithVtsDefaultSettings(MotTest $motTest)
    {
        return null === $motTest->getBrakeTestResult() &&
            $motTest->getTestTypeCode() != MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
    }

    /**
     * @param ViewModel                                                                         $brakeTestConfigurationViewModel
     * @param BrakeTestResultClass1And2|null                                                    $brakeTestResult
     * @param BrakeTestConfigurationClass1And2Helper|BrakeTestConfigurationHelperInterface|null $configHelper
     */
    private function setViewModelDataForGroupA(
        ViewModel $brakeTestConfigurationViewModel,
        $brakeTestResult,
        BrakeTestConfigurationClass1And2Helper $configHelper
    ) {
        if (null !== $brakeTestResult) {
            $brakeTestConfigurationViewModel->setVariable('brakeTestType', $brakeTestResult->getBrakeTestTypeCode());
            $brakeTestResultDataSource = $brakeTestResult;
        } else {
            $brakeTestConfigurationViewModel->setVariable('brakeTestType', $configHelper->getBrakeTestType());
            $brakeTestResultDataSource = $configHelper;
        }

        $brakeTestConfigurationViewModel->setVariables(
            [
                'vehicleWeightFront', $brakeTestResultDataSource->getVehicleWeightFront(),
                'vehicleWeightRear', $brakeTestResultDataSource->getVehicleWeightRear(),
                'riderWeight', $brakeTestResultDataSource->getRiderWeight(),
                'isSidecarAttached', $brakeTestResultDataSource->getSidecarWeight() ? true : false,
                'sidecarWeight', $brakeTestResultDataSource->getSidecarWeight(),

            ]
        );
    }

    /**
     * @param ViewModel                                  $brakeTestConfigurationViewModel
     * @param DvsaVehicle                                $vehicle
     * @param BrakeTestConfigurationClass3AndAboveHelper $configHelper
     * @param bool                                       $preselectBrakeTestWeight
     */
    private function setViewModelDataForGroupB(
        ViewModel $brakeTestConfigurationViewModel,
        DvsaVehicle $vehicle,
        BrakeTestConfigurationClass3AndAboveHelper $configHelper,
        $preselectBrakeTestWeight
    ) {
        $brakeTestConfigurationViewModel->setVariables(
            [
                'configHelper' => $configHelper,
                'showVehicleType' => $this->displayConfigurationVehicleType($vehicle),
                'weightTypes' => $this->getGroupBWeightTypes($vehicle->getVehicleClass()->getCode()),
                'preselectBrakeTestWeight' => $preselectBrakeTestWeight,
            ]
        );
    }

    /**
     * @param BrakeTestConfigurationClass1And2Dto | BrakeTestConfigurationClass3AndAboveDto | BrakeTestConfigurationDtoInterface $dto
     * @param MotTest                                                                                                            $motTest
     * @param $vehicleClass
     */
    private function populateBrakeTestTypeWithSiteDefaults(&$dto, MotTest $motTest, $vehicleClass)
    {
        /** @var VehicleTestingStationDto $site */
        $site = $this->getVehicleTestingStationDtoById($motTest->getSiteId());

        if (!$site) {
            // No data to populate from
            return;
        }

        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            if (!empty($site->getDefaultBrakeTestClass1And2())) {
                $dto->setBrakeTestType($site->getDefaultBrakeTestClass1And2()->getCode());
            }
        } else {
            if (!empty($site->getDefaultParkingBrakeTestClass3AndAbove())) {
                $dto->setParkingBrakeTestType($site->getDefaultParkingBrakeTestClass3AndAbove()->getCode());
            }

            if (!empty($site->getDefaultServiceBrakeTestClass3AndAbove())) {
                $dto->setServiceBrake1TestType($site->getDefaultServiceBrakeTestClass3AndAbove()->getCode());
            }
        }
    }

    /**
     * @param $vtsId
     *
     * @return VehicleTestingStationDto|null
     */
    private function getVehicleTestingStationDtoById($vtsId)
    {
        $url = UrlBuilder::of()->vehicleTestingStation()->routeParam('id', $vtsId)->toString();
        try {
            $response = $this->restClient->get($url);

            return $response['data'];
        } catch (GeneralRestException $ex) {
            return null;
        }
    }

    /**
     * @param bool $isVehicleBikeType
     *
     * @return BrakeTestConfigurationMapperInterface
     */
    private function getBrakeTestMapperService($isVehicleBikeType)
    {
        if ($isVehicleBikeType) {
            return new BrakeTestConfigurationClass1And2Mapper();
        } else {
            return new BrakeTestConfigurationClass3AndAboveMapper();
        }
    }

    /**
     * @param string $vehicleClass
     *
     * @return array
     */
    private function getGroupBWeightTypes($vehicleClass)
    {
        switch ($vehicleClass) {
            case VehicleClassCode::CLASS_3:
            case VehicleClassCode::CLASS_4:
                return [
                    WeightSourceCode::VSI => self::VEHICLE_WEIGHT_TYPE__BRAKE_TEST_WEIGHT,
                    WeightSourceCode::PRESENTED => self::VEHICLE_WEIGHT_TYPE__PRESENTED_WEIGHT,
                    WeightSourceCode::NOT_APPLICABLE => self::VEHICLE_WEIGHT_TYPE__NOT_KNOWN,
                ];
            case VehicleClassCode::CLASS_5:
                return [
                    WeightSourceCode::DGW_MAM => self::VEHICLE_WEIGHT_TYPE__DGW_MAM,
                    WeightSourceCode::CALCULATED => self::VEHICLE_WEIGHT_TYPE__CALCULATED_WEIGHT,
                ];
            case VehicleClassCode::CLASS_7:
                return [
                    WeightSourceCode::DGW => self::VEHICLE_WEIGHT_TYPE__DGW,
                    WeightSourceCode::PRESENTED => self::VEHICLE_WEIGHT_TYPE__PRESENTED_WEIGHT,
                ];
            default:
                throw new \InvalidArgumentException(sprintf("Unrecognised Group B vehicle class: '%s'", $vehicleClass));
        }
    }

    /**
     * @param AbstractActionResult $actionResult
     * @param $messages
     *
     * @return $this
     */
    protected function addErrorMessages(AbstractActionResult $actionResult, $messages)
    {
        if (is_array($messages)) {
            $actionResult->addErrorMessages($messages);
        } else {
            $actionResult->addErrorMessage($messages);
        }

        return $this;
    }

    /**
     * @param DvsaVehicle $vehicle
     *
     * @return bool
     */
    protected function displayConfigurationVehicleType(DvsaVehicle $vehicle)
    {
        $vehicleFirstUsedDate = new \DateTime($vehicle->getFirstUsedDate());
        $preVehicleTypeCheckDate = new \DateTime(self::VEHICLE_TYPE_CHECK_START_DATE);

        return $this->isMotTestVehicleClass($vehicle, VehicleClassCode::CLASS_4)
            && $vehicleFirstUsedDate >= $preVehicleTypeCheckDate;
    }

    /**
     * @param DvsaVehicle $vehicle
     * @param string      $expectedVehicleClassCode
     *
     * @return bool
     */
    protected function isMotTestVehicleClass(DvsaVehicle $vehicle, $expectedVehicleClassCode)
    {
        return $this->getVehicleClassCodeFromMotTest($vehicle) === $expectedVehicleClassCode;
    }

    /**
     * @param DvsaVehicle $vehicle
     *
     * @return string
     */
    protected function getVehicleClassCodeFromMotTest(DvsaVehicle $vehicle)
    {
        return $vehicle->getVehicleClass()->getCode();
    }
}
