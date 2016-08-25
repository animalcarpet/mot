<?php

namespace DvsaMotTest\Controller;

use Application\Helper\PrgHelper;
use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleUnderTestRequest;
use Application\Service\ContingencySessionManager;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use DvsaCommon\Auth\Assertion\RefuseToTestAssertion;
use DvsaCommon\Auth\PermissionInSystem;
use Core\Service\RemoteAddress;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Dto\Vehicle\AbstractVehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\Constants\VehicleSearchSource;
use Vehicle\Helper\ColoursContainer;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use DvsaMotTest\ViewModel\StartTestConfirmationViewModel;
use Dvsa\Mot\ApiClient\Service\VehicleService;

/**
 * Class StartTestConfirmationController.
 */
class StartTestConfirmationController extends AbstractDvsaMotTestController
{
    const ROUTE_START_TEST_CONFIRMATION = 'start-test-confirmation';
    const ROUTE_START_RETEST_CONFIRMATION = 'start-retest-confirmation';

    const ROUTE_PARAM_NO_REG = 'noRegistration';
    const ROUTE_PARAM_ID = 'id';
    const ROUTE_PARAM_SOURCE = 'source';
    const RETEST_GRANTED_CHECK_RESULT = 0;

    /** @var Request */
    protected $request;
    protected $vehicleId;

    /** @var string */
    protected $obfuscatedVehicleId;
    protected $noRegistration;
    protected $vehicleSource;
    protected $vtsId;

    /** @var string mot test type */
    protected $method;
    protected $eligibilityNotices;
    /** @var AbstractVehicleDto */
    protected $vehicleDetails;
    protected $inProgressTestExists;

    protected $isEligibleForRetest;

    /** @var \DvsaCommon\Obfuscate\ParamObfuscator */
    protected $paramObfuscator;

    /** @var  PrgHelper */
    private $prgHelper;

    /** @var StartTestConfirmationViewModel */
    private $startTestConfirmationViewModel;

    /** @param \DvsaCommon\Obfuscate\ParamObfuscator $paramObfuscator */
    public function __construct(ParamObfuscator $paramObfuscator)
    {
        $this->paramObfuscator = $paramObfuscator;
        $this->startTestConfirmationViewModel = new StartTestConfirmationViewModel();
    }

    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $method = $request->getQuery('retest') ? MotTestTypeCode::RE_TEST : MotTestTypeCode::NORMAL_TEST;

        return $this->commonAction($method);
    }

    public function retestAction()
    {
        return $this->commonAction(MotTestTypeCode::RE_TEST);
    }

    public function trainingAction()
    {
        return $this->commonAction(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
    }

    /**
     * @param string $method MOT_TEST_TYPE
     *
     * @return \Zend\Http\Response|ViewModel
     */
    protected function commonAction($method)
    {
        $this->prgHelper = new PrgHelper($this->request);
        if ($this->prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($this->prgHelper->getRedirectUrl());
        }

        if ($method !== MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->assertGranted(PermissionInSystem::MOT_TEST_START);
        }

        $this->method = $method;
        $this->request = $this->getRequest();

        $this->processParams();
        $this->getVehicleDetails();
        $this->findIfInProgressTestExists();

        return $this->processRequest();
    }

    /**
     * @throws \Exception
     */
    protected function processParams()
    {
        $this->obfuscatedVehicleId = (string)$this->params()->fromRoute(self::ROUTE_PARAM_ID, 0);

        $this->vehicleId = $this->paramObfuscator->deobfuscateEntry(
            ParamObfuscator::ENTRY_VEHICLE_ID, $this->obfuscatedVehicleId, false
        );

        $noRegistrationString = $this->params()->fromRoute(self::ROUTE_PARAM_NO_REG);
        $this->noRegistration = ($noRegistrationString === '1');

        $this->vehicleSource = $this->params()->fromRoute(self::ROUTE_PARAM_SOURCE);

        if ($this->method === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->vtsId = null;

            return;
        }

        $currentVts = $this->getIdentity()->getCurrentVts();
        if (!$currentVts) {
            throw new \Exception("VTS not found");
        }
        $this->vtsId = $currentVts->getVtsId();
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    protected function processRequest()
    {
        if ($this->request->isPost()) {

            if ($this->needToCollectOneTimePassword()) {
                return $this->createOtpViewModel();
            }

            $contingencySessionManager = $this->getContingencySessionManager();

            try {
                $pagingFromStartTestConfirm = $this->request->getPost('startTestConfirm');
                $newMotTestId = $this->createNewTestFromPost();

                $url = $contingencySessionManager->isMotContingency() ?
                    MotTestUrlBuilderWeb::motTest($newMotTestId) :
                    MotTestUrlBuilderWeb::options($newMotTestId);

                $this->prgHelper->setRedirectUrl($url->toString());

                return $this->redirect()->toUrl($url);
            } catch (OtpApplicationException $e) {
                if (isset($pagingFromStartTestConfirm)) {
                    $errorData = null;
                } else {
                    $errorData = $e->getErrorData();

                    if (isset($errorData['message'])) {
                        $message = $errorData['message'];
                        $this->addErrorMessages($message);
                    }
                }

                return $this->createOtpViewModel($errorData);
            } catch (RestApplicationException $e) {
                if ($this->isRetest() && ($e instanceof ValidationException)) {
                    $this->isEligibleForRetest = false;
                    $this->eligibilityNotices = $e->getDisplayMessages();
                } else {
                    $this->addErrorMessages($e->getDisplayMessages());
                }
            }
        }

        return $this->createViewModel();
    }

    protected function createNewTestFromPost()
    {
        if ($this->isRetest()) {
            $apiUrl = MotTestUrlBuilder::retest();
        } elseif ($this->method === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $apiUrl = MotTestUrlBuilder::demoTest();
        } else {
            $apiUrl = MotTestUrlBuilder::motTest();
        }

        $result = $this->getRestClient()->post($apiUrl->toString(), $this->prepareNewTestDataFromPostForPhpApi());
        $createMotTestResult = $result['data'];
        $motTestNumber = $createMotTestResult['motTestNumber'];

        return $motTestNumber;
    }

    /**
     *
     * @return bool
     */
    private function needToCollectOneTimePassword()
    {
        $oneTimePassword = $this->request->getPost('oneTimePassword');

        if (isset($oneTimePassword)) {
            return false;
        }

        $changeConfirmed = $this->request->getPost('changeConfirmed') === '1';

        if (
            !$this->getAuthorizationService()->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP) &&
            $this->needToUpdateDvsaVehicleUnderTest() && $changeConfirmed === false
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function needToUpdateDvsaVehicleUnderTest()
    {
        if (MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING === $this->method) {
            return false;
        }

        $targetVehicle = $this->getVehicleDetails();

        $selectedColourName = $this->mapIdToName('colours', $this->request->getPost('colourId'));
        $selectedSecondaryColourName = $this->mapIdToName('colours', $this->request->getPost('secondaryColourId'));
        $selectedFuelTypeName = $this->mapIdToName('fuelTypes', $this->request->getPost('fuelTypeId'));
        $selectedVehicleClass = $this->request->getPost('vehicleClassId');

        if (
            $targetVehicle->getColour() !== $selectedColourName ||
            $targetVehicle->getColourSecondary() !== $selectedSecondaryColourName ||
            $targetVehicle->getFuelType() !== $selectedFuelTypeName ||
            $targetVehicle->getVehicleClass() !== $selectedVehicleClass
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @todo: to be removed once new API can create mot test!
     * @return array|null Array of new Test data or Null if was not POST
     */
    protected function prepareNewTestDataFromPostForPhpApi()
    {
        $request = $this->request;

        $vehicleIdKey = $this->isVehicleSource(VehicleSearchSource::DVLA) ? 'dvlaVehicleId' : 'vehicleId';

        $primaryColour = $request->getPost('colourId');
        $secondaryColour = $request->getPost('secondaryColourId');
        $fuelTypeId = $request->getPost('fuelTypeId');

        if (MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING === $this->method) {
            $primaryColour = $this->mapNameToCode('colours', $primaryColour);
            $secondaryColour = $this->mapNameToCode('colours', $secondaryColour);
            $fuelTypeId = $this->mapNameToCode('fuelTypes', $fuelTypeId);
        } else {
            $primaryColour = $this->mapIdToCode('colours', $primaryColour);
            $secondaryColour = $this->mapIdToCode('colours', $secondaryColour);
            $fuelTypeId = $this->mapIdToCode('fuelTypes', $fuelTypeId);
        }

        $data = [
            $vehicleIdKey => $this->vehicleId,
            'vehicleTestingStationId' => $this->vtsId,
            'primaryColour' => $primaryColour,
            'secondaryColour' => $secondaryColour,
            'fuelTypeId' => $fuelTypeId,
            'vehicleClassCode' => intval($request->getPost('vehicleClassId')),
            'hasRegistration' => !$this->noRegistration,
            'oneTimePassword' => $request->getPost('oneTimePassword'),
            'motTestType' => $request->getPost('motTestType', $this->method)
        ];

        $contingencySessionManager = $this->getContingencySessionManager();
        if ($contingencySessionManager->isMotContingency()) {
            $contingencySession = $contingencySessionManager->getContingencySession();

            $data += [
                'contingencyId' => $contingencySession['contingencyId'],
                'contingencyDto' => DtoHydrator::dtoToJson($contingencySession['dto']),
            ];
        }

        return $data;
    }

    /**
     * @see self::mapCatalogEntities
     * @param string $listName
     * @param string $code
     * @return string
     */
    private function mapCodeToName($listName, $code)
    {
        return $this->mapCatalogEntities($listName, $code, 'code', 'name');
    }

    /**
     * @see self::mapCatalogEntities
     * @param string $listName
     * @param string $id
     * @return string
     */
    private function mapIdToName($listName, $id)
    {
        return $this->mapCatalogEntities($listName, $id, 'id', 'name');
    }

    /**
     * @see self::mapCatalogEntities
     * @param string $listName
     * @param string $id
     * @return string
     */
    private function mapIdToCode($listName, $id)
    {
        return $this->mapCatalogEntities($listName, $id);
    }

    /**
     * @see self::mapCatalogEntities
     * @param string $listName
     * @param string $name
     * @return string
     */
    private function mapNameToCode($listName, $name)
    {
        return $this->mapCatalogEntities($listName, $name, 'name', 'code');
    }

    /**
     * Temporary solution to properties on existing multi-dimensional catalogs,
     * this should addressed by improvement on our single dimension, automatically generated Enums
     *
     * @param string $listName
     * @param int $lookupValue
     * @return string
     * @param string $from
     * @param string $to
     * @throws NotFoundException
     */
    private function mapCatalogEntities($listName, $lookupValue, $from = 'id', $to = 'code')
    {
        $list = $this->getCatalogService()->getData()[$listName];

        foreach ($list as $item) {
            if ($item[$from] == $lookupValue) {
                return $item[$to];
            }
        }

        throw new \RuntimeException(
            sprintf('Failed to matching %s for %s %s in the list of %s', $to, $from, $lookupValue, $listName)
        );
    }

    /**
     * @return string
     */
    protected function getClientIp()
    {
        return RemoteAddress::getIp();
    }

    /**
     * @return array
     */
    protected function prepareViewData()
    {
        $viewData = [
            'vehicleDetails' => $this->vehicleDetails,
            'checkExpiryResults' => null,
            'staticData' => $this->getStaticData(),
            'prgHelper' => $this->prgHelper,
        ];

        $viewModel = $this->startTestConfirmationViewModel;
        $viewModel->setMethod($this->method);
        $viewModel->setObfuscatedVehicleId($this->obfuscatedVehicleId);
        $viewModel->setNoRegistration($this->noRegistration);
        $viewModel->setVehicleSource($this->vehicleSource);
        $viewModel->setInProgressTestExists($this->inProgressTestExists);
        $viewModel->setSearchVrm($this->params()->fromQuery('searchVrm', ''));
        $viewModel->setSearchVin($this->params()->fromQuery('searchVin', ''));
        $viewModel->setCanRefuseToTest(false, false);

        $motContingency = $this->getContingencySessionManager()->isMotContingency();
        $viewModel->setMotContingency($motContingency);

        if ($viewModel->isRetest()
            || ($viewModel->isNormalTest() && $viewModel->getVehicleSource() == VehicleSearchSource::VTR)
        ) {
            if ($this->isEligibleForRetest === null) {
                $this->checkEligibilityForRetest();
            }

            if ($this->isEligibleForRetest) {
                $viewModel->setMethod(MotTestTypeCode::RE_TEST);
                $viewModel->setEligibleForRetest(true);
            }

            $viewModel->setEligibilityNotices($this->eligibilityNotices);
        } else {
            $viewModel->setEligibleForRetest(false);
        }

        if ($viewModel->isRetest() || $viewModel->isNormalTest()) {
            $viewData['checkExpiryResults'] = $this->getCheckExpiryResults();

            $viewModel->setCanRefuseToTest(
                $this->isEligibleForRetest,
                $this->createRefuseToTestAssertion()->isGranted($this->vtsId)
            );
        }

        if ($viewModel->isRetest()) {
            $this->isRetest();
            $this->method = MotTestTypeCode::RE_TEST;
        }

        $viewData['viewModel'] = $viewModel;

        return $viewData;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    protected function createViewModel()
    {
        $viewModel = new ViewModel($this->prepareViewData());

        if (in_array(
            $this->method,
            [
                MotTestTypeCode::NORMAL_TEST,
                MotTestTypeCode::RE_TEST,
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
            ],
            true
        )
        ) {
            $viewModel->setTemplate('dvsa-mot-test/start-test-confirmation/index.phtml');
        }

        if ($this->method == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->layout()->setVariable('pageSubTitle', 'Training test');
        } else {
            $this->layout()->setVariable('pageSubTitle', 'MOT testing');
        }

        return $viewModel;
    }

    /**
     * @param $otpErrorData
     *
     * @return ViewModel
     */
    protected function createOtpViewModel($otpErrorData)
    {
        $viewModel = $this->createViewModel();

        // Override sub title for the Vehicle Details Changed screen
        $this->layout()->setVariable('pageSubTitle', null);

        $request = $this->getRequest();

        $viewModel->setVariables([
                'colourId' => $request->getPost('colourId'),
                'secondaryColourId' => $request->getPost('secondaryColourId'),
                'fuelTypeId' => $request->getPost('fuelTypeId'),
                'vehicleClassId' => intval($request->getPost('vehicleClassId')),
            ]
        );

        $viewModel->setTemplate('dvsa-mot-test/start-test-confirmation/vehicle-change-confirmation.phtml');

        return $viewModel;
    }

    protected function isVehicleSource($type)
    {
        return ($this->vehicleSource === $type);
    }

    protected function getVehicleDetails($flush = false, $source = VehicleSearchSource::DVLA)
    {
        if ($flush || is_null($this->vehicleDetails)) {
            if ($this->isVehicleSource($source)) {
                $this->vehicleDetails = $this->getVehicleServiceClient()->getDvlaVehicleById((int)$this->vehicleId);
            } else {
                $this->vehicleDetails = $this->getVehicleServiceClient()->getDvsaVehicleById((int)$this->vehicleId);
            }
        }

        return $this->vehicleDetails;
    }

    protected function findIfInProgressTestExists()
    {
        if ($this->isVehicleSource(VehicleSearchSource::DVLA)
            || $this->method === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        ) {
            return;
        }

        $apiUrl = VehicleUrlBuilder::vehicle($this->vehicleId)->testInProgressCheck();
        $apiResult = $this->getRestClient()->get($apiUrl);

        if (!empty($apiResult['data'])) {
            $this->inProgressTestExists = $apiResult['data'];
        }
    }

    /**
     * @return null|mixed
     */
    protected function getCheckExpiryResults()
    {
        $isDvlaVehicle = ($this->vehicleDetails ? $this->vehicleDetails instanceof DvlaVehicle : false);

        $apiUrl = VehicleUrlBuilder::testExpiryCheck($this->vehicleId, $isDvlaVehicle);

        $contingencySessionManager = $this->getContingencySessionManager();
        if ($contingencySessionManager->isMotContingency() === true) {
            /** @var ContingencyTestDto $contingency */
            $contingencyTestDto = $contingencySessionManager->getContingencySession()['dto'];

            if ($contingencyTestDto instanceof ContingencyTestDto) {
                $apiUrl->queryParam('contingencyDatetime',
                    $contingencyTestDto->getPerformedAt()->format(DateUtils::DATETIME_FORMAT));
            }
        }

        $apiResult = $this->getRestClient()->get($apiUrl);

        if (!empty($apiResult['data']['checkResult'])) {
            return $apiResult['data']['checkResult'];
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getStaticData()
    {
        /** @var CatalogService $catalogService */
        $catalogService = $this->getCatalogService();

        return [
            'colours' => new ColoursContainer($catalogService->getColoursWithIds(), false, true),
            'fuelTypes' => $catalogService->getFuelTypesWithId(),
            'emptyVrmReasons' => $catalogService->getReasonsForEmptyVRM(),
            'emptyVinReasons' => $catalogService->getReasonsForEmptyVIN(),
        ];
    }

    protected function checkEligibilityForRetest()
    {
        $data = [];

        $ctSessionMng = $this->getContingencySessionManager();
        if ($ctSessionMng->isMotContingency()) {
            $contingencySession = $ctSessionMng->getContingencySession();

            $data += [
                'contingencyDto' => DtoHydrator::dtoToJson($contingencySession['dto']),
            ];
        }

        try {
            $apiUrl = VehicleUrlBuilder::retestEligibilityCheck($this->vehicleId, $this->vtsId);
            $apiResult = $this->getRestClient()->post($apiUrl, $data);

            $this->isEligibleForRetest = ($apiResult['data']['isEligible'] === true);

            if (false === $this->isEligibleForRetest) {
                $this->eligibilityNotices = $apiResult['data']['reasons'];
            }

        } catch (ValidationException $e) {
            $this->isEligibleForRetest = false;
            $this->eligibilityNotices = $e->getDisplayMessages();
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }
    }

    /**
     * @throws \LogicException
     *
     * @return bool
     */
    protected function isRetest()
    {
        if (!isset($this->method)) {
            throw new \LogicException("Method should be set first");
        }

        return $this->method === MotTestTypeCode::RE_TEST;
    }

    /**
     * @return RefuseToTestAssertion
     */
    protected function createRefuseToTestAssertion()
    {
        return new RefuseToTestAssertion($this->getAuthorizationService());
    }

    /**
     * @return ContingencySessionManager
     */
    protected function getContingencySessionManager()
    {
        return $this->serviceLocator->get(ContingencySessionManager::class);
    }

    /**
     * @return VehicleService
     */
    private function getVehicleServiceClient()
    {
        return $this->getServiceLocator()->get(VehicleService::class);
    }
}
