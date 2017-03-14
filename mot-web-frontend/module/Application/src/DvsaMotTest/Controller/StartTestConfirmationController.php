<?php

namespace DvsaMotTest\Controller;

use Application\Helper\PrgHelper;
use Application\Service\ContingencySessionManager;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use Core\Routing\MotTestRouteList;
use Core\Routing\VehicleRoutes;
use Core\Service\RemoteAddress;
use Dvsa\Mot\ApiClient\Resource\Item\AbstractVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\Assertion\RefuseToTestAssertion;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaMotTest\Helper\DvsaVehicleBuilder;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\ViewModel\StartTestConfirmationViewModel;
use Vehicle\TestingAdvice\Assertion\ShowTestingAdviceAssertion;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

/**
 * Class StartTestConfirmationController.
 */
class StartTestConfirmationController extends AbstractDvsaMotTestController
{
    const ROUTE_START_TEST_CONFIRMATION = 'start-test-confirmation';

    const ROUTE_PARAM_NO_REG = 'noRegistration';
    const ROUTE_PARAM_ID = 'id';
    const ROUTE_PARAM_SOURCE = 'source';
    const RETEST_GRANTED_CHECK_RESULT = 0;

    const UNKNOWN_TEST = 'Unknown';

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
    /** @var  AbstractVehicle */
    protected $vehicleDetails;
    protected $inProgressTestExists;

    protected $isEligibleForRetest;

    /** @var \DvsaCommon\Obfuscate\ParamObfuscator */
    protected $paramObfuscator;

    /** @var  PrgHelper */
    private $prgHelper;

    /** @var StartTestConfirmationViewModel */
    private $startTestConfirmationViewModel;

    private $countryOfRegistrationCatalog;

    private $vehicleService;
    /** @var  StartTestChangeService */
    private $startTestChangeService;

    /**
     * @param ParamObfuscator $paramObfuscator
     * @param CountryOfRegistrationCatalog $countryOfRegistrationCatalog
     * @param VehicleService $vehicleService
     * @param StartTestChangeService $startTestChangeService
     */
    public function __construct(
        ParamObfuscator $paramObfuscator,
        CountryOfRegistrationCatalog $countryOfRegistrationCatalog,
        VehicleService $vehicleService,
        StartTestChangeService $startTestChangeService
    )
    {
        $this->paramObfuscator = $paramObfuscator;
        $this->startTestConfirmationViewModel = new StartTestConfirmationViewModel();
        $this->countryOfRegistrationCatalog = $countryOfRegistrationCatalog;
        $this->vehicleService = $vehicleService;
        $this->startTestChangeService = $startTestChangeService;
    }

    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $method = $request->getQuery('retest') ? MotTestTypeCode::RE_TEST : MotTestTypeCode::NORMAL_TEST;
        $this->startTestChangeService->saveChange(StartTestChangeService::URL, ['url' => MotTestRouteList::MOT_TEST_START_TEST]);

        return $this->commonAction($method);
    }

    public function retestAction()
    {
        return $this->commonAction(MotTestTypeCode::RE_TEST);
    }

    public function trainingAction()
    {
        $this->startTestChangeService->saveChange(StartTestChangeService::URL, ['url' => MotTestRouteList::MOT_TEST_START_TRAINING_TEST]);

        return $this->commonAction(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
    }

    public function nonMotTestAction()
    {
        $this->assertGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);

        $this->startTestChangeService->saveChange(StartTestChangeService::URL, ['url' => MotTestRouteList::MOT_TEST_START_NON_MOT_TEST]);

        return $this->commonAction(MotTestTypeCode::NON_MOT_TEST);
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

        $this->setBreadcrumbs(['breadcrumbs' => ['MOT testing' => '']]);

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
        $this->startTestChangeService->saveChange(StartTestChangeService::NO_REGISTRATION, ['noRegistration' => $noRegistrationString]);

        $this->vehicleSource = $this->params()->fromRoute(self::ROUTE_PARAM_SOURCE);
        $this->startTestChangeService->saveChange(StartTestChangeService::SOURCE, ['source' => $this->vehicleSource]);

        if ($this->method === MotTestTypeCode::NON_MOT_TEST) {
            $this->vtsId = null;

            return;
        }

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

            if ($this->vehicleDetails instanceof DvlaVehicle &&
                !$this->isTestClassSetForDvlaVehicle()) {
                    return $this->createViewModel();
            }

            $contingencySessionManager = $this->getContingencySessionManager();

            try {
                $newMotTestId = $this->createNewTestFromPost();

                $url = $contingencySessionManager->isMotContingency() ?
                    MotTestUrlBuilderWeb::motTest($newMotTestId) :
                    MotTestUrlBuilderWeb::options($newMotTestId);

                $this->prgHelper->setRedirectUrl($url->toString());

                return $this->redirect()->toUrl($url);
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
        } elseif ($this->isNotMotTest()) {
            $apiUrl = MotTestUrlBuilder::nonMotTest();
        } else {
            $apiUrl = MotTestUrlBuilder::motTest();
        }

        $result = $this->getRestClient()->post($apiUrl->toString(), $this->prepareNewTestDataFromPostForPhpApi());
        $createMotTestResult = $result['data'];
        $motTestNumber = $createMotTestResult['motTestNumber'];

        return $motTestNumber;
    }

    /**
     * @todo: to be removed once new API can create mot test!
     * @return array|null Array of new Test data or Null if was not POST
     */
    protected function prepareNewTestDataFromPostForPhpApi()
    {
        $vehicleIdKey = $this->isVehicleSource(VehicleSearchSource::DVLA) ? 'dvlaVehicleId' : 'vehicleId';

        $isColourChangedInSession = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COLOUR);
        $colourDetailsFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COLOUR);
        $isFuelChangedInSession = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_ENGINE);
        $fuelDetailsFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_ENGINE);
        $isMakeAndModelChanged = $this->startTestChangeService->isMakeAndModelChanged();
        $makeDetailsFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MAKE);
        $modelDetailsFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MODEL);
        $isCountryOfRegistationChangedInSession = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COUNTRY);

        $primaryColour = $isColourChangedInSession
            ? $colourDetailsFromSession[StartTestChangeService::PRIMARY_COLOUR]
            : $this->vehicleDetails->getColour()->getCode();
        $secondaryColour = $isColourChangedInSession
            ? $colourDetailsFromSession[StartTestChangeService::SECONDARY_COLOUR]
            : $this->vehicleDetails->getColourSecondary()->getCode();
        $fuelTypeId = $isFuelChangedInSession
            ? $fuelDetailsFromSession[StartTestChangeService::FUEL_TYPE]
            : $this->vehicleDetails->getFuelType()->getCode();
        $cylinderCapacity = $isFuelChangedInSession
            ? $fuelDetailsFromSession[StartTestChangeService::CYLINDER_CAPACITY]
            : $this->vehicleDetails->getCylinderCapacity();
        $vehicleClassCode = $this->startTestChangeService
            ->isValueChanged(StartTestChangeService::CHANGE_CLASS)
            ? $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_CLASS)
            : $this->vehicleDetails->getVehicleClass()->getCode();
        $vehicleMake = $isMakeAndModelChanged
            ? $makeDetailsFromSession
            : null;
        $vehicleModel = $isMakeAndModelChanged
            ? $modelDetailsFromSession
            : null;
        $countryOfRegistration = $isCountryOfRegistationChangedInSession
            ? $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COUNTRY)
            : $this->vehicleDetails->getCountryOfRegistrationId();

        if (is_null($secondaryColour) || empty($secondaryColour)) {
            $secondaryColour = ColourCode::NOT_STATED;
        }

        $data = [
            $vehicleIdKey => $this->vehicleId,
            'vehicleTestingStationId' => $this->vtsId,
            'primaryColour' => $primaryColour,
            'secondaryColour' => $secondaryColour,
            'fuelTypeId' => $fuelTypeId,
            'cylinderCapacity' => $cylinderCapacity,
            'countryOfRegistration' => $countryOfRegistration,
            'vehicleMake' => $vehicleMake,
            'vehicleModel' => $vehicleModel,
            'vehicleClassCode' => intval($vehicleClassCode),
            'hasRegistration' => !$this->noRegistration,
            'motTestType' => $this->request->getPost('motTestType', $this->method),
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
            'prgHelper' => $this->prgHelper,
        ];

        $viewModel = $this->startTestConfirmationViewModel;
        $viewModel->setMethod($this->method)
            ->setObfuscatedVehicleId($this->obfuscatedVehicleId)
            ->setNoRegistration($this->noRegistration)
            ->setVehicleSource($this->vehicleSource)
            ->setInProgressTestExists($this->inProgressTestExists)
            ->setSearchVrm($this->params()->fromQuery('searchVrm', ''))
            ->setSearchVin($this->params()->fromQuery('searchVin', ''))
            ->setCanRefuseToTest(false, false)
            ->setMakeAndModel(
                $this->startTestChangeService->isMakeAndModelChanged()
                    ? strtoupper($this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MAKE)['makeName'])
                    : $this->vehicleDetails->getMakeName(),
                $this->startTestChangeService->isMakeAndModelChanged()
                    ? strtoupper($this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_MODEL)['modelName'])
                    : $this->vehicleDetails->getModelName())
            ->setEngine(
                $this
                    ->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_ENGINE)
                    ? $this->setFuelTypeFromSessionData()->getFuelType()
                    : $this->vehicleDetails->getFuelType(),
                $this
                    ->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_ENGINE)
                    ? $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_ENGINE)['cylinderCapacity']
                    : $this->vehicleDetails->getCylinderCapacity())
            ->setCompoundedColour(
                $this
                    ->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COLOUR)
                    ? $this->setPrimaryColourFromSessionData()->getColour()
                    : $this->vehicleDetails->getColour(),
                $this
                    ->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COLOUR)
                    ? $this->setSecondaryColourFromSessionData()->getColourSecondary()
                    : $this->vehicleDetails->getColourSecondary())
            ->setFirstUsedDate($this->vehicleDetails->getFirstUsedDate());

        if ($this->vehicleDetails instanceof DvsaVehicle) {
            $viewModel->setIsMysteryShopper($this->isMysteryShopper());

            if (!empty($this->vehicleDetails->getWeight())) {
                $viewModel->setBrakeTestWeight($this->vehicleDetails->getWeight() . ' ' . 'kg');
            } else {
                $viewModel->setBrakeTestWeight(self::UNKNOWN_TEST);
            }
            if (!empty($this->vehicleDetails->getVehicleClass()) && !empty($this->vehicleDetails->getVehicleClass()->getCode())) {
                $viewModel->setMotTestClass($this->vehicleDetails->getVehicleClass()->getName());
            } else {
                $viewModel->setMotTestClass(self::UNKNOWN_TEST);
            }
        } else {
            $viewModel->setBrakeTestWeight(self::UNKNOWN_TEST);
            $viewModel->setMotTestClass(self::UNKNOWN_TEST);
        }

        if ($this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_CLASS)) {
            $viewModel->setMotTestClass($this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_CLASS));
        }

        $isCountryOfRegistrationChanged = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COUNTRY);
        $countryOfRegistrationFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COUNTRY);
        $country = $this->countryOfRegistrationCatalog->getByCode(
            $isCountryOfRegistrationChanged
                ? $countryOfRegistrationFromSession
                : $this->vehicleDetails->getCountryOfRegistrationId()
        );
        if (isset($country)) {
            $viewModel->setCountryOfRegistration($country->getName());
        } else {
            $viewModel->setCountryOfRegistration($this->countryOfRegistrationCatalog->getByCode(CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN)->getName());
        }

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

            $viewModel->setMotExpirationDate($viewData['checkExpiryResults']['expiryDate']);
        }

        if ($viewModel->isRetest()) {
            $this->isRetest();
            $this->method = MotTestTypeCode::RE_TEST;
        }

        if ($this->isVehicleSource(VehicleSearchSource::VTR) && (new ShowTestingAdviceAssertion($this->vehicleService))->isGranted($this->vehicleId, $this->method)) {
            $viewModel->setTestingAdviceUrl(
                VehicleRoutes::of($this->url())->testingAdviceWithParams(
                    $this->obfuscatedVehicleId,
                    $this->noRegistration,
                    $this->vehicleSource
                )
            );
        }
        $this->setGdsDataLayer($viewModel->hasTestingAdvice());

        $viewData['viewModel'] = $viewModel;

        return $viewData;
    }

    /**
     * @param boolean $hasTestingAdvice
     */
    private function setGdsDataLayer($hasTestingAdvice) {
        $this->gtmDataLayer(['testingadvice' => var_export($hasTestingAdvice, true)]);
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
                MotTestTypeCode::NON_MOT_TEST,
            ],
            true
        )
        ) {
            $viewModel->setTemplate('dvsa-mot-test/start-test-confirmation/index.phtml');
            $this->layout('layout/layout-govuk.phtml');

            if (!$this->startTestConfirmationViewModel->isInProgressTestExists()) {
                $this->layout()->setVariable('pageTitle', 'Confirm vehicle and start test');
            }
            if ($this->startTestConfirmationViewModel->isRetest()) {
                $this->layout()->setVariable('pageTitle', 'Confirm vehicle for retest');
            }
        }

        if ($this->method == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->layout()->setVariable('pageSubTitle', 'Training test');
        } elseif ($this->method == MotTestTypeCode::NON_MOT_TEST) {
            $this->layout()->setVariable('pageSubTitle', 'Non-MOT test');
        } else {
            if (!$this->startTestConfirmationViewModel->isInProgressTestExists()) {
                $this->layout()->setVariable('pageSubTitle', 'MOT testing');
            }
        }

        return $viewModel;
    }

    protected function isVehicleSource($type)
    {
        return ($this->vehicleSource === $type);
    }

    /**
     * @param bool $flush
     * @param string $source
     * @return DvlaVehicle|DvsaVehicle
     */
    protected function getVehicleDetails($flush = false, $source = VehicleSearchSource::DVLA)
    {
        if ($flush || is_null($this->vehicleDetails)) {
            if ($this->isVehicleSource($source)) {
                $this->vehicleDetails = $this->getVehicleServiceClient()->getDvlaVehicleById((int)$this->vehicleId);
            } else {
                $this->vehicleDetails = $this->getVehicleServiceClient()->getDvsaVehicleById((int)$this->vehicleId);

                if ($this->vehicleDetails !== null) {
                    $this->vehicleDetails->setWeight($this->getMotTestServiceClient()->getVehicleTestWeight((int)$this->vehicleId));
                }
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

        $this->inProgressTestExists = $this->getMotTestServiceClient()->isVehicleUnderTest($this->vehicleId);
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

    private function isNotMotTest()
    {
        if (!isset($this->method)) {
            throw new \LogicException("Method should be set first");
        }

        return $this->method === MotTestTypeCode::NON_MOT_TEST;
    }

    /**
     * @return bool
     */
    private function isMysteryShopper()
    {
        if (null === $this->getVehicleDetails()) {
            return false;
        }

        return (true === $this->vehicleDetails->getIsIncognito());
    }

    private function setPrimaryColourFromSessionData()
    {
        $coloursName = '';
        $colour = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COLOUR)['primaryColour'];

        foreach ($this->getCatalogByName('colours') as $id => $code) {
            if ($code['code'] == $colour) {
                $coloursName = $code;
            }
        }

        $newVehicleColourDetails = (new DvsaVehicleBuilder())->getEmptyVehicleStdClass();

        $colour = new \stdClass();
        $colour->code = $coloursName['code'];
        $colour->name = $coloursName['name'];
        $newVehicleColourDetails->colour = $colour;

        return (new DvsaVehicle($newVehicleColourDetails));
    }

    private function setSecondaryColourFromSessionData()
    {
        $coloursName = '';
        $colour = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COLOUR)['secondaryColour'];

        foreach ($this->getCatalogByName('colours') as $id => $code) {
            if ($code['code'] == $colour) {
                $coloursName = $code;
            }
        }

        $newVehicleColourDetails = (new DvsaVehicleBuilder())->getEmptyVehicleStdClass();

        $colour = new \stdClass();
        $colour->code = $coloursName['code'];
        $colour->name = $coloursName['name'];
        $newVehicleColourDetails->colourSecondary = $colour;

        return (new DvsaVehicle($newVehicleColourDetails));
    }

    private function setFuelTypeFromSessionData()
    {
        $fuelName = '';
        $fuelCodeFromSession = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_ENGINE);

        foreach ($this->getCatalogByName('fuelTypes') as $id => $code) {
            if ($code['code'] == $fuelCodeFromSession['fuelType']) {
                $fuelName = $code;
            }
        }

        $newVehicleEngineDetails = (new DvsaVehicleBuilder())->getEmptyVehicleStdClass();

        $engineType = new \stdClass();
        $engineType->code = $fuelName['code'];
        $engineType->name = $fuelName['name'];
        $newVehicleEngineDetails->fuelType = $engineType;
        return (new DvsaVehicle($newVehicleEngineDetails));
    }

    private function isTestClassSetForDvlaVehicle()
    {
        if ($this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_CLASS)) {
            $this->startTestConfirmationViewModel->setNoTestClassSetOnSubmission(false);
            return true;
        } else {
            $this->startTestConfirmationViewModel->setNoTestClassSetOnSubmission(true);
            return false;
        }
    }
}
