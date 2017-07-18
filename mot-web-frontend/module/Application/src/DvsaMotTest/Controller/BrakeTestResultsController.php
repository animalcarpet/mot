<?php

namespace DvsaMotTest\Controller;

use Application\Service\ContingencySessionManager;
use Core\Action\AbstractRedirectActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass1And2;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass1And2Mapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Mapper\BrakeTestResultToArrayConverter;
use DvsaMotTest\Model\BrakeTestResultClass1And2ViewModel;
use DvsaMotTest\Model\BrakeTestResultClass3AndAboveViewModel;
use DvsaMotTest\View\Model\MotTestTitleModel;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
use Zend\View\Model\ViewModel;

/**
 * Class BrakeTestResultsController.
 */
class BrakeTestResultsController extends AbstractDvsaMotTestController
{
    const VEHICLE_TYPE_CHECK_START_DATE = '2010-09-01';

    const TEMPLATE_ADD_CLASS_1_2 = 'dvsa-mot-test/brake-test-results/add-brake-test-results-class12';
    const TEMPLATE_ADD_CLASS_3_AND_ABOVE
        = 'dvsa-mot-test/brake-test-results/add-brake-test-results-class3-and-above';

    const ROUTE_MOT_TEST_BRAKE_TEST_RESULTS = 'mot-test/brake-test-results';
    const ROUTE_MOT_TEST_BRAKE_TEST_SUMMARY = 'mot-test/brake-test-summary';

    const BRAKES_PERFORMANCE_CATEGORY_ID_CLASS_1_AND_2 = 120;
    const BRAKES_PERFORMANCE_CATEGORY_ID = 5430;

    const PAGE_TITLE__BRAKE_TEST_CONFIGURATION = 'Brake test configuration';

    /** @var SubmitBrakeTestConfigurationAction $submitBrakeTestConfigurationAction */
    private $submitBrakeTestConfigurationAction;

    /** @var ViewBrakeTestConfigurationAction $viewBrakeTestConfigurationAction */
    private $viewBrakeTestConfigurationAction;

    /** @var BrakeTestConfigurationClass3AndAboveMapper */
    private $brakeTestConfigurationClass3AndAboveMapper;

    public function __construct(
        SubmitBrakeTestConfigurationAction $submitBrakeTestConfigurationAction,
        ViewBrakeTestConfigurationAction $viewBrakeTestConfigurationAction,
        BrakeTestConfigurationClass3AndAboveMapper $brakeTestConfigurationClass3AndAboveMapper
    ) {
        $this->submitBrakeTestConfigurationAction = $submitBrakeTestConfigurationAction;
        $this->viewBrakeTestConfigurationAction = $viewBrakeTestConfigurationAction;
        $this->brakeTestConfigurationClass3AndAboveMapper = $brakeTestConfigurationClass3AndAboveMapper;
    }

    public function configureBrakeTestAction()
    {
        $request = $this->getRequest();

        $submitAction = $this->submitBrakeTestConfigurationAction;
        $viewAction = $this->viewBrakeTestConfigurationAction;

        $motTestNumber = $this->getMotTestNumber();

        $submitActionResult = null;
        $submitData = [];

        if ($request->isPost()) {
            $submitData = $request->getPost()->toArray();
            $submitActionResult = $submitAction->execute($submitData, $motTestNumber);

            if ($submitActionResult instanceof AbstractRedirectActionResult) {
                return $this->applyActionResult($submitActionResult);
            }
        }

        if ($submitActionResult !== null) {
            $viewAction->setPreviousActionResult($submitActionResult, $submitData);
        }

        $viewActionResult = $viewAction->execute($motTestNumber);

        return $this->applyActionResult($viewActionResult);
    }

    public function addBrakeTestResultsAction()
    {
        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);

        /** @var DvsaVehicle $vehicle */
        $vehicle = $this->getVehicleCurrentlyUnderTest($motTest);

        if ($request->isPost()) {
            $vehicleClass = $request->getPost()->get('vehicleClass');
        } else {
            if (!$motTest) {
                return $this->getFlashErrorViewModel();
            }

            $this->getPerformMotTestAssertion()->assertGranted($motTest);
            $vehicleClass = $vehicle->getVehicleClass()->getCode();
        }

        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            return $this->addBrakeTestResultsClass12($motTest, $vehicle);
        }

        return $this->addBrakeTestResultsClass3AndAbove($motTest, $vehicle);
    }

    /**
     * @param MotTest|null     $motTest
     * @param DvsaVehicle|null $vehicle
     *
     * @return \Zend\Http\Response|ViewModel
     */
    private function addBrakeTestResultsClass3AndAbove(MotTest $motTest = null, DvsaVehicle $vehicle = null)
    {
        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();

        $brakeTestResult = null;

        /** @var BrakeTestConfigurationContainerHelper $brakeTestConfigurationContainer */
        $brakeTestConfigurationContainer = $this->getServiceLocator()->get('BrakeTestConfigurationContainerHelper');
        $queryData = $brakeTestConfigurationContainer->fetchConfig();

        /** @var BrakeTestConfigurationClass3AndAboveDto $configDto */
        $configDto = $this->brakeTestConfigurationClass3AndAboveMapper->mapToDto($queryData);

        if ($request->isPost()) {
            $brakeTestResult = new BrakeTestResultClass3AndAboveViewModel(
                $configDto,
                null,
                null,
                $request->getPost()->getArrayCopy()
            );

            try {
                $this->getBrakeTestResultsResource()->save($motTestNumber, $brakeTestResult->toArray());

                return $this->redirect()->toRoute(
                    self::ROUTE_MOT_TEST_BRAKE_TEST_SUMMARY,
                    ['motTestNumber' => $motTestNumber]
                );
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        } else {
            $apiBrakeTestResultAsArray = $this->prepareDataForViewModel($motTest, $vehicle);

            $brakeTestResult = new BrakeTestResultClass3AndAboveViewModel(
                $configDto,
                null,
                $apiBrakeTestResultAsArray,
                null
            );

            // (temp) Inject missing fields
            // Inject serviceBrake1Data, fetched from mot-test-service to the old "Brake Test Result" model
            $brakeTestResult->setServiceBrake1Data($motTest);
        }

        if (!$motTest) {
            /** @var MotTest $motTest */
            $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        }

        $viewModel = new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motTestNumber' => $motTestNumber,
                'vehicle' => $vehicle,
                'brakeTestResult' => $brakeTestResult,
                'brakeTestConfiguration' => $brakeTestResult->getBrakeTestConfiguration(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'motTest' => $motTest,
            ]
        );

        $viewModel->setTemplate(self::TEMPLATE_ADD_CLASS_3_AND_ABOVE);

        return $viewModel;
    }

    /**
     * @param MotTest|null     $motTest
     * @param DvsaVehicle|null $vehicle
     *
     * @return \Zend\Http\Response|ViewModel
     */
    private function addBrakeTestResultsClass12(MotTest $motTest = null, DvsaVehicle $vehicle = null)
    {
        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();

        $brakeTestResult = null;

        $brakeTestConfigurationContainer = $this->getServiceLocator()->get('BrakeTestConfigurationContainerHelper');
        $queryData = $brakeTestConfigurationContainer->fetchConfig();

        $configDto = (new BrakeTestConfigurationClass1And2Mapper())->mapToDto($queryData);

        if ($request->isPost()) {
            $brakeTestResult = new BrakeTestResultClass1And2ViewModel(
                $configDto,
                null,
                $request->getPost()->getArrayCopy());

            try {
                $this->getBrakeTestResultsResource()->save($motTestNumber, $brakeTestResult->toArray());

                return $this->redirect()->toRoute(
                    self::ROUTE_MOT_TEST_BRAKE_TEST_SUMMARY,
                    ['motTestNumber' => $motTestNumber]
                );
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        } else {
            $brakeTestResult = new BrakeTestResultClass1And2ViewModel(
                $configDto,
                null,
                $request->getPost()->getArrayCopy());

            // (temp) Inject missing fields
            // Inject brake test result, fetched from mot-test-service to the old model
            $brakeTestResult->setBrakeTestResult($motTest);
        }

        if (!$motTest) {
            /** @var MotTest $motTest */
            $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        }

        $viewModel = new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motTestNumber' => $motTestNumber,
                'vehicle' => $vehicle,
                'brakeTestResult' => $brakeTestResult,
                'brakeTestConfiguration' => $brakeTestResult->getBrakeTestConfiguration(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'motTest' => $motTest,
            ]
        );

        $viewModel->setTemplate(self::TEMPLATE_ADD_CLASS_1_2);

        return $viewModel;
    }

    public function displayBrakeTestSummaryAction()
    {
        $motTestNumber = $this->getMotTestNumber();
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);

        /** @var DvsaVehicle $vehicle */
        $vehicle = $this->getVehicleCurrentlyUnderTest($motTest);

        /** @var DvsaVehicleViewModel $vehicleViewModel */
        $vehicleViewModel = new DvsaVehicleViewModel($vehicle);

        $this->getPerformMotTestAssertion()->assertGranted($motTest);

        if ($motTest->getTestTypeCode() === MotTestTypeCode::RE_TEST) {
            /** @var MotTest $previousMotTest */
            $previousMotTest = $this->tryGetMotTestOrAddErrorMessages($motTest->getMotTestOriginalNumber());
            $motTest->setPreviousMotTest($previousMotTest);
        }

        if (VehicleClassGroup::isGroupA($vehicleViewModel->getVehicleClass()->getCode())) {
            $showParkingBrakeImbalance = false;
            $showAxleTwoParkingBrakeImbalance = false;
        } else {
            if ($motTest->getPreviousMotTest() !== null && !is_null($motTest->getPreviousMotTest()->getBrakeTestResult())) {
                $brakeTestResultClass3 = new BrakeTestResultClass3AndAbove($motTest->getPreviousMotTest()->getBrakeTestResult());
            } else {
                $brakeTestResultClass3 = new BrakeTestResultClass3AndAbove($motTest->getBrakeTestResult());
            }

            $showParkingBrakeImbalance = $brakeTestResultClass3->getServiceBrakeIsSingleLine();

            $showAxleTwoParkingBrakeImbalance = $showParkingBrakeImbalance
                && $brakeTestResultClass3->getParkingBrakeSecondaryImbalance();
        }

        $brakeRfrId = $this->getBrakesPerformanceCategoryId($vehicle);
        $breakPerformanceDefectsUrl = $this->getBrakesPerformanceDefectsUrl($brakeRfrId, $motTestNumber);

        return new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'vehicleViewModel' => $vehicleViewModel,
                'motTest' => $motTest,
                'showParkingBrakeImbalance' => $showParkingBrakeImbalance,
                'showAxleTwoParkingBrakeImbalance' => $showAxleTwoParkingBrakeImbalance,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'breakPerformanceDefectsUrl' => $breakPerformanceDefectsUrl,
            ]
        );
    }

    protected function getMotTestNumber()
    {
        return $this->params()->fromRoute('motTestNumber', null);
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
     * @return BrakeTestResultsResource
     */
    private function getBrakeTestResultsResource()
    {
        return $this->getServiceLocator()->get(BrakeTestResultsResource::class);
    }

    /**
     * @return WebPerformMotTestAssertion
     */
    private function getPerformMotTestAssertion()
    {
        return $this->getServiceLocator()->get(WebPerformMotTestAssertion::class);
    }

    /**
     * @return ContingencySessionManager
     */
    private function getContingencySessionManager()
    {
        return $this->serviceLocator->get(ContingencySessionManager::class);
    }

    /**
     * Generate URL for breaks performance defects category.
     *
     * @param int $brakeRfrId
     * @param int $motTestNumber
     *
     * @return string
     */
    private function getBrakesPerformanceDefectsUrl($brakeRfrId, $motTestNumber)
    {
        return $this->url()->fromRoute(
            'mot-test-defects/categories/category',
            [
                'categoryId' => $brakeRfrId,
                'motTestNumber' => $motTestNumber,
            ]
        );
    }

    /**
     * Determinate the defects category ID based on vehicle class.
     *
     * @param DvsaVehicle $dvsaVehicle
     *
     * @return int
     */
    private function getBrakesPerformanceCategoryId(DvsaVehicle $dvsaVehicle)
    {
        $vehicleClass = $dvsaVehicle->getVehicleClass()->getCode();

        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            return self::BRAKES_PERFORMANCE_CATEGORY_ID_CLASS_1_AND_2;
        }

        return self::BRAKES_PERFORMANCE_CATEGORY_ID;
    }

    /**
     * Converts stdClass from MotTest response object and converts it to appropriate BrakeTestResultClass*
     * This should be fixed on api client / mot-test service level soon™...
     *
     * @param \stdClass $brakeTestResult
     * @param $vehicleClass
     *
     * @return BrakeTestResultClass1And2|BrakeTestResultClass3AndAbove
     */
    private function convertApiResponseToBrakeTestResultClass(\stdClass $brakeTestResult, $vehicleClass)
    {
        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            return new BrakeTestResultClass1And2($brakeTestResult);
        }

        return new BrakeTestResultClass3AndAbove($brakeTestResult);
    }

    /**
     * @param MotTest     $motTest
     * @param DvsaVehicle $vehicle
     *
     * @return array
     */
    private function prepareDataForViewModel(MotTest $motTest, DvsaVehicle $vehicle)
    {
        $vehicleClass = $vehicle->getVehicleClass()->getCode();
        $brakeTestResultData = $motTest->getBrakeTestResult();

        if (null === $brakeTestResultData) {
            return [];
        }
        $brakeTestResultResponse = $this->convertApiResponseToBrakeTestResultClass($motTest->getBrakeTestResult(), $vehicleClass);
        $apiBrakeTestResultAsArray = BrakeTestResultToArrayConverter::convert($brakeTestResultResponse);

        return $apiBrakeTestResultAsArray;
    }

    /**
     * @param MotTest $motTest
     *
     * @return DvsaVehicle
     */
    private function getVehicleCurrentlyUnderTest(MotTest $motTest)
    {
        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());

        return $vehicle;
    }
}
