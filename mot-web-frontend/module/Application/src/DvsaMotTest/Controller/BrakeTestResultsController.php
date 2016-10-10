<?php

namespace DvsaMotTest\Controller;

use Application\Service\ContingencySessionManager;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass1And2Mapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Mapper\BrakeTestConfigurationMapperInterface;
use DvsaMotTest\Model\BrakeTestConfigurationClass1And2Helper;
use DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper;
use DvsaMotTest\Model\BrakeTestConfigurationHelperInterface;
use DvsaMotTest\Model\BrakeTestResultClass1And2ViewModel;
use DvsaMotTest\Model\BrakeTestResultClass3AndAboveViewModel;
use DvsaCommon\Enum\BrakeTestTypeCode;
use Zend\View\Model\ViewModel;
use DvsaMotTest\View\Model\MotTestTitleModel;

/**
 * Class BrakeTestResultsController
 */
class BrakeTestResultsController extends AbstractDvsaMotTestController
{
    const VEHICLE_TYPE_CHECK_START_DATE = '2010-09-01';

    const TEMPLATE_CONFIG_CLASS_1_2 = 'dvsa-mot-test/brake-test-results/brake-test-configuration-class12';
    const TEMPLATE_CONFIG_CLASS_3_AND_ABOVE
        = 'dvsa-mot-test/brake-test-results/brake-test-configuration-class3-and-above';
    const TEMPLATE_ADD_CLASS_1_2 = 'dvsa-mot-test/brake-test-results/add-brake-test-results-class12';
    const TEMPLATE_ADD_CLASS_3_AND_ABOVE = 'dvsa-mot-test/brake-test-results/add-brake-test-results-class3-and-above';

    const ROUTE_MOT_TEST_BRAKE_TEST_RESULTS = 'mot-test/brake-test-results';
    const ROUTE_MOT_TEST_BRAKE_TEST_SUMMARY = 'mot-test/brake-test-summary';

    const BREAKS_PERFORMANCE_CATEGORY_ID_CLASS_1_AND_2 = 120;
    const BREAKS_PERFORMANCE_CATEGORY_ID = 5430;

    public function configureBrakeTestAction()
    {
        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();

        if ($request->isPost()) {
            $vehicleClass  = $request->getPost()->get('vehicleClass');
            $isVehicleBikeType = $this->isMotTestVehicleBikeType($vehicleClass);
            $configDtoMapper = $this->getBrakeTestMapperService($isVehicleBikeType);

            $data = $request->getPost()->getArrayCopy();
            $dto = $configDtoMapper->mapToDto($data);

            try {
                $this->postConfigurationDataToApiForValidation($dto, $motTestNumber);
                return $this->redirectToAddBrakeTestResult($data, $motTestNumber);
            } catch (RestApplicationException $e) {
                if ($e->containsError(InvalidTestStatus::getMessage(MotTestStatusName::ABORTED))) {
                    $this->addErrorMessages([InvalidTestStatus::getMessage(MotTestStatusName::ABORTED)]);
                    return $this->redirect()->toRoute(MotTestController::ROUTE_MOT_TEST, ['motTestNumber' => $motTestNumber]);
                } elseif ($e->containsError(InvalidTestStatus::getMessage(MotTestStatusName::FAILED))) {
                    $this->addErrorMessages([InvalidTestStatus::getMessage(MotTestStatusName::FAILED)]);
                    return $this->redirect()->toRoute(MotTestController::ROUTE_MOT_TEST, ['motTestNumber' => $motTestNumber]);
                }
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }


        /** @var MotTestDto $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        $brakeTestResult = $motTest->getBrakeTestResult();

        if (!$request->isPost()) {
            $isVehicleBikeType = $this->isMotTestVehicleBikeType($motTest->getVehicle()->getClassCode());
            $configDtoMapper = $this->getBrakeTestMapperService($isVehicleBikeType);
            $dto = $configDtoMapper->mapToDefaultDto($motTest);
        }
        $this->getPerformMotTestAssertion()->assertGranted($motTest);

        if ($motTest->getStatus() !== MotTestStatusName::ACTIVE) {
            $this->addErrorMessages([InvalidTestStatus::getMessage($motTest->getStatus())]);

            return $this->redirect()->toRoute(MotTestController::ROUTE_MOT_TEST, ['motTestNumber' => $motTestNumber]);
        }

        $configHelper = $this->getConfigHelperService($isVehicleBikeType);
        $configHelper->setConfigDto($dto);

        $preselectBrakeTestWeight = false;
        if ($motTest->getVehicle()->getClassCode() !== VehicleClassCode::CLASS_1 && $motTest->getVehicle()->getClassCode() !== VehicleClassCode::CLASS_2) {
            $hasCorrectServiceBrakeTestType = in_array($configHelper->getServiceBrakeTestType(),[BrakeTestTypeCode::PLATE, BrakeTestTypeCode::ROLLER]);
            $hasCorrectParkingBrakeTestType = in_array($configHelper->getParkingBrakeTestType(),[BrakeTestTypeCode::PLATE, BrakeTestTypeCode::ROLLER]);


            if ($configHelper->getVehicleWeight() && ($hasCorrectServiceBrakeTestType || $hasCorrectParkingBrakeTestType)) {
                $preselectBrakeTestWeight = true;
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setVariable('isMotContingency', $this->getContingencySessionManager()->isMotContingency());
        $viewModel->setVariable('brakeTestTypes', array_reverse($this->getCatalogService()->getBrakeTestTypes()));
        $viewModel->setVariable('motTest', $motTest);
        $viewModel->setVariable('showVehicleType', $this->displayConfigurationVehicleType($motTest));
        $viewModel->setVariable('configHelper', $configHelper);
        $viewModel->setVariable('brakeTestResult', $brakeTestResult);
        $viewModel->setVariable('motTestTitleViewModel', (new MotTestTitleModel()));
        $viewModel->setVariable('preselectBrakeTestWeight', $preselectBrakeTestWeight);

        if ($isVehicleBikeType) {
            $viewModel->setVariable('brakeTestType',
                ArrayUtils::tryGet($brakeTestResult,'brakeTestType',$configHelper->getBrakeTestType()));
            $viewModel->setVariable('vehicleWeightFront',
                ArrayUtils::tryGet($brakeTestResult,'vehicleWeightFront',$configHelper->getVehicleWeightFront()));
            $viewModel->setVariable('vehicleWeightRear',
                ArrayUtils::tryGet($brakeTestResult,'vehicleWeightRear',$configHelper->getVehicleWeightRear()));
            $viewModel->setVariable('riderWeight',
                ArrayUtils::tryGet($brakeTestResult,'riderWeight',$configHelper->getRiderWeight()));
            $viewModel->setVariable('isSidecarAttached',(isset($brakeTestResult['sidecarWeight']) ? 1 : 0));
            $viewModel->setVariable('sidecarWeight',
                ArrayUtils::tryGet($brakeTestResult,'sidecarWeight',$configHelper->getSidecarWeight()));
        }

        $viewModel->setTemplate($this->getConfigViewModelTemplate($isVehicleBikeType));

        return $viewModel;
    }

    private function postConfigurationDataToApiForValidation($dto, $motTestNumber)
    {
        $apiUrl = UrlBuilder::of()->motTest()->routeParam('motTestNumber', $motTestNumber)
            ->brakeTestResult()->validateConfiguration();
        $this->getRestClient()->postJson($apiUrl, DtoHydrator::dtoToJson($dto));
    }

    private function redirectToAddBrakeTestResult(array $brakeTestConfiguration, $motTestNumber)
    {
        $brakeTestConfigurationContainer = $this->getServiceLocator()->get('BrakeTestConfigurationContainerHelper');
        $brakeTestConfigurationContainer->persistConfig($brakeTestConfiguration);
        return $this->redirect()->toRoute(
            self::ROUTE_MOT_TEST_BRAKE_TEST_RESULTS,
            ['motTestNumber' => $motTestNumber]
        );
    }

    public function addBrakeTestResultsAction()
    {
        $motTestNumber = $this->getMotTestNumber();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $vehicleClass = $request->getPost()->get('vehicleClass');
            $motTest = null;
        }
        else {
            /** @var MotTestDto $motTest */
            $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);

            if (!$motTest) {
                return $this->getFlashErrorViewModel();
            }

            $this->getPerformMotTestAssertion()->assertGranted($motTest);
            $vehicleClass = $motTest->getVehicle()->getClassCode();
        }

        if ($this->isMotTestVehicleBikeType($vehicleClass)) {
            return $this->addBrakeTestResultsClass12($motTest);
        }

        return $this->addBrakeTestResultsClass3AndAbove($motTest);
    }

    private function addBrakeTestResultsClass3AndAbove(MotTestDto $motTest = null)
    {

        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();

        $brakeTestResult = null;

        $brakeTestConfigurationContainer = $this->getServiceLocator()->get('BrakeTestConfigurationContainerHelper');
        $queryData = $brakeTestConfigurationContainer->fetchConfig();

        $configDto = (new BrakeTestConfigurationClass3AndAboveMapper)->mapToDto($queryData);

        if ($request->isPost()) {
            $brakeTestResult = new BrakeTestResultClass3AndAboveViewModel(
                $configDto,
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
            $brakeTestResult = new BrakeTestResultClass3AndAboveViewModel(
                $configDto,
                $motTest->getBrakeTestResult(),
                null
            );
        }

        if (!$motTest) {
            /** @var MotTestDto $motTest */
            $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        }
        /** @var VehicleDto $vehicle */
        $vehicle = $motTest->getVehicle();

        $viewModel = new ViewModel(
            [
                'isMotContingency'       => $this->getContingencySessionManager()->isMotContingency(),
                'motTestNumber'          => $motTestNumber,
                'vehicle'                => $vehicle,
                'brakeTestResult'        => $brakeTestResult,
                'brakeTestConfiguration' => $brakeTestResult->getBrakeTestConfiguration(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'motTest' => $motTest
            ]
        );
        $viewModel->setTemplate(self::TEMPLATE_ADD_CLASS_3_AND_ABOVE);

        return $viewModel;
    }

    private function addBrakeTestResultsClass12(MotTestDto $motTest = null)
    {
        $request = $this->getRequest();
        $motTestNumber = $this->getMotTestNumber();

        $brakeTestResult = null;

        $brakeTestConfigurationContainer = $this->getServiceLocator()->get('BrakeTestConfigurationContainerHelper');
        $queryData = $brakeTestConfigurationContainer->fetchConfig();

        $configDto = (new BrakeTestConfigurationClass1And2Mapper)->mapToDto($queryData);

        if ($request->isPost()) {
            $brakeTestResult = new BrakeTestResultClass1And2ViewModel($configDto, $request->getPost()->getArrayCopy());

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
            $brakeTestResult = new BrakeTestResultClass1And2ViewModel($configDto, $motTest->getBrakeTestResult());
        }

        if (!$motTest) {
            /** @var MotTestDto $motTest */
            $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        }

        $viewModel = new ViewModel(
            [
                'isMotContingency'       => $this->getContingencySessionManager()->isMotContingency(),
                'motTestNumber'          => $motTestNumber,
                'vehicle'                => $motTest->getVehicle(),
                'brakeTestResult'        => $brakeTestResult,
                'brakeTestConfiguration' => $brakeTestResult->getBrakeTestConfiguration(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'motTest' => $motTest
            ]
        );
        $viewModel->setTemplate(self::TEMPLATE_ADD_CLASS_1_2);

        return $viewModel;
    }

    public function displayBrakeTestSummaryAction()
    {
        $motTestNumber = $this->getMotTestNumber();
        /** @var MotTestDto $motTest */
        $motTest = $this->getMotTestFromApi($motTestNumber);

        $this->getPerformMotTestAssertion()->assertGranted($motTest);

        $showParkingBrakeImbalance
            = isset($motTest->getBrakeTestResult()['serviceBrakeIsSingleLine']) ?
            $motTest->getBrakeTestResult()['serviceBrakeIsSingleLine'] : false;
        $showAxleTwoParkingBrakeImbalance = $showParkingBrakeImbalance
            && !empty($motTest->getBrakeTestResult()['parkingBrakeSecondaryImbalance']);

        $brakeRfrId = $this->getBreaksPerfomanceCategoryId($motTest);
        $breakPerformanceDefectsUrl = $this->getBreaksPerformanceDefectsUrl($brakeRfrId, $motTestNumber);


        return new ViewModel(
            [
                'isMotContingency'                 => $this->getContingencySessionManager()->isMotContingency(),
                'motTest'                          => $motTest,
                'showParkingBrakeImbalance'        => $showParkingBrakeImbalance,
                'showAxleTwoParkingBrakeImbalance' => $showAxleTwoParkingBrakeImbalance,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'breakPerformanceDefectsUrl' => $breakPerformanceDefectsUrl
            ]
        );
    }

    protected function getMotTestNumber()
    {
        return $this->params()->fromRoute('motTestNumber', null);
    }

    protected function isMotTestVehicleBikeType($vehicleClass)
    {
        return $vehicleClass == VehicleClassCode::CLASS_1
            || $vehicleClass == VehicleClassCode::CLASS_2;
    }

    /**
     * @param MotTestDto $motTest
     * @param string     $expectedVehicleClassCode
     *
     * @return bool
     */
    protected function isMotTestVehicleClass(MotTestDto $motTest, $expectedVehicleClassCode)
    {
        return $this->getVehicleClassCodeFromMotTest($motTest) === $expectedVehicleClassCode;
    }

    protected function getVehicleClassCodeFromMotTest(MotTestDto $motTest)
    {
        $vehicle = $motTest->getVehicle();

        return $vehicle->getClassCode();
    }

    protected function displayConfigurationVehicleType(MotTestDto $motTest)
    {
        $vehicle = $motTest->getVehicle();
        $vehicleFirstUsedDate = new \DateTime($vehicle->getFirstUsedDate());
        $preVehicleTypeCheckDate = new \DateTime(self::VEHICLE_TYPE_CHECK_START_DATE);

        return $this->isMotTestVehicleClass($motTest, VehicleClassCode::CLASS_4)
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
     * @param bool $isVehicleBikeType
     *
     * @return BrakeTestConfigurationMapperInterface
     */
    private function getBrakeTestMapperService($isVehicleBikeType)
    {
        if ($isVehicleBikeType) {
            return $this->getServiceLocator()->get(BrakeTestConfigurationClass1And2Mapper::class);
        } else {
            return $this->getServiceLocator()->get(BrakeTestConfigurationClass3AndAboveMapper::class);
        }
    }

    /**
     * @param bool $isVehicleBikeType
     *
     * @return BrakeTestConfigurationHelperInterface
     */
    private function getConfigHelperService($isVehicleBikeType)
    {
        if ($isVehicleBikeType) {
            return $this->getServiceLocator()->get(BrakeTestConfigurationClass1And2Helper::class);
        } else {
            return $this->getServiceLocator()->get(BrakeTestConfigurationClass3AndAboveHelper::class);
        }
    }

    /**
     * @param bool $isVehicleBikeType
     *
     * @return string
     */
    private function getConfigViewModelTemplate($isVehicleBikeType)
    {
        if ($isVehicleBikeType) {
            return self::TEMPLATE_CONFIG_CLASS_1_2;
        } else {
            return self::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE;
        }
    }

    /**
     * @return ContingencySessionManager
     */
    private function getContingencySessionManager()
    {
        return $this->serviceLocator->get(ContingencySessionManager::class);
    }

    /**
     * Generate url for breaks perfomance defects category
     *
     * @param $brakeRfrId
     * @param $motTestNumber
     * @return string
     */
    private function getBreaksPerformanceDefectsUrl($brakeRfrId, $motTestNumber)
    {
        $isNewDefectsFfEnabled = $this->getFeatureToggles()->isEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS);

        // new defects / RFRs category view
        if ($isNewDefectsFfEnabled === true) {
            return $this->url()->fromRoute(
                'mot-test-defects/categories/category',
                [
                    'categoryId' => $brakeRfrId,
                    'motTestNumber' => $motTestNumber,
                ]
            );
        }

        return $this->url()->fromRoute(
            'mot-test/test-item-selector',
            [
                'tis-id' => $brakeRfrId,
                'motTestNumber' => $motTestNumber,
            ]
        );
    }

    /**
     * Determinate the defects category id based on vechile class
     *
     * @param MotTestDto $motTest
     * @return int
     */
    private function getBreaksPerfomanceCategoryId(MotTestDto $motTest)
    {
        $vehicle = $motTest->getVehicle();
        $vehicleClass = $vehicle->getClassCode();

        if (in_array($vehicleClass, [VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2])) {
            return self::BREAKS_PERFORMANCE_CATEGORY_ID_CLASS_1_AND_2;
        }

        return self::BREAKS_PERFORMANCE_CATEGORY_ID;
    }
}
