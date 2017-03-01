<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Controller;

use Application\Helper\PrgHelper;
use Application\Service\ContingencySessionManager;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Request\Validator\Exception;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\MotTestModule\Controller\MotTestResultsController;
use Dvsa\Mot\Frontend\MotTestModule\Listener\MotEvents;
use DvsaCommon\ApiClient\MotTest\DuplicateCertificate\Dto\MotTestDuplicateCertificateEditAllowedDto;
use DvsaCommon\ApiClient\MotTest\DuplicateCertificate\MotTestDuplicateCertificateApiResource;
use DvsaCommon\Auth\Assertion\AbandonVehicleTestAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\DuplicateCertificateSearchType;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\Network;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\ReasonForCancelDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException as HttpRestJsonValidationException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\MysteryShopper\MysteryShopperExpiryDateGenerator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\UrlBuilder\ReportUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Validation\ValidationException;
use DvsaCommon\Validation\ValidationResult;
use DvsaCommonApi\Service\Exception\UnauthenticatedException;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTest\Model\OdometerUpdate;
use DvsaMotTest\View\Model\MotPrintModel;
use DvsaMotTest\View\Model\MotTestTitleModel;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class MotTestController.
 */
class MotTestController extends AbstractDvsaMotTestController
{
    const DATE_FORMAT = 'j F Y';
    const DATETIME_FORMAT = 'd M Y H:i';

    const ODOMETER_VALUE_REQUIRED_MESSAGE = "Odometer value must be entered to update odometer reading";
    const ODOMETER_FORM_ERROR_MESSAGE = "The odometer reading should be a valid number between 0 and 999999";
    const TEST_DOCUMENT_VT30 = 'VT30';
    const TEST_DOCUMENT_VT32 = 'VT32';

    const ROUTE_MOT_TEST = 'mot-test';
    const ROUTE_MOT_TEST_SHORT_SUMMARY = 'mot-test/short-summary';

    const ERROR_NO_SITE_FOR_NON_MOT_TEST = 'Uh oh, no site for non-mot test';


    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /** @var EventManager $eventManager */
    private $eventManager;

    /**
     * @var OdometerReadingViewObject
     */
    private $odometerViewObject;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /** @var MotTestDuplicateCertificateApiResource $duplicateCertificateApiResource */
    private $duplicateCertificateApiResource;

    /**
     * MotTestController constructor.
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param EventManager $eventManager
     * @param OdometerReadingViewObject $odometerViewObject
     * @param MotTestDuplicateCertificateApiResource $duplicateCertificateApiResource
     * @param FeatureToggles $featureToggles
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        EventManager $eventManager,
        OdometerReadingViewObject $odometerViewObject,
        MotTestDuplicateCertificateApiResource $duplicateCertificateApiResource,
        FeatureToggles $featureToggles
    ) {
        $this->authorisationService = $authorisationService;
        $this->eventManager = $eventManager;
        $this->odometerViewObject = $odometerViewObject;
        $this->duplicateCertificateApiResource = $duplicateCertificateApiResource;
        $this->featureToggles = $featureToggles;
    }

    public function indexAction()
    {
        $motTestNumber = $this->params('motTestNumber', 0);

        if (true === $this->isFeatureEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)) {
            return $this->forward()->dispatch(
                MotTestResultsController::class,
                [
                    'action' => 'index',
                    'motTestNumber' => $motTestNumber,
                ]
            );
        }

        $isDemo = false;

        /** @var MotTest $motTest */
        $motTest = $this->getMotTestFromApi($motTestNumber);

        try {
            $this->getPerformMotTestAssertion()->assertGranted($motTest);
            $testType = $motTest->getTestTypeCode();
            $isDemo = MotTestType::isDemo($testType);
            $isTester = $this->getAuthorizationService()->isTester();
            $currentVts = $this->getIdentity()->getCurrentVts();

            if (!$isDemo && $isTester && !$currentVts) {
                return $this->redirectToSelectLocation($motTestNumber);
            };

            $apiUrl = MotTestUrlBuilder::odometerReadingNotices($motTestNumber)->toString();
            $readingNotices = $this->getRestClient()->get($apiUrl);

            $this->addTestNumberAndTypeToGtmDataLayer($motTestNumber, $testType);
        } catch (HttpRestJsonValidationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        if ($motTest instanceof MotTest && $motTest->getOdometerValue() !== null) {
            $this->odometerViewObject->setValue($motTest->getOdometerValue());
            $this->odometerViewObject->setUnit($motTest->getOdometerUnit());
            $this->odometerViewObject->setResultType($motTest->getOdometerResultType());
        }

        if (!empty($readingNotices)) {
            $this->odometerViewObject->setNotices($readingNotices['data']);
        }

        if ($this->isMysteryShopper($motTest)) {
            $mysteryShopperExpiryDate = (new MysteryShopperExpiryDateGenerator())->getCertificateExpiryDate();
            $mysteryShopperExpiryDate = DateTimeApiFormat::date($mysteryShopperExpiryDate);
            $motTest->setExpiryDate($mysteryShopperExpiryDate);
        }

        return $this->createViewModel(
            'dvsa-mot-test/mot-test/index.phtml',
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motTest' => $motTest,
                'isDemo' => $isDemo,
                'odometerReading' => $this->odometerViewObject,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        );
    }

    /**
     * @return WebPerformMotTestAssertion
     */
    private function getPerformMotTestAssertion()
    {
        return $this->getServiceLocator()->get(WebPerformMotTestAssertion::class);
    }

    public function updateOdometerAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);

        if ($request->isPost()) {
            $odometerForm = $this->getForm(new OdometerUpdate());
            $odometerForm->setData($request->getPost());

            if ($odometerForm->isValid()) {
                try {
                    $validatedData = $odometerForm->getData();
                    $readingResultType = $validatedData['resultType'];
                    if ($readingResultType === OdometerReadingResultType::OK) {
                        if (!isset($validatedData['odometer']) || trim($validatedData['odometer']) === '') {
                            $this->addErrorMessages(self::ODOMETER_VALUE_REQUIRED_MESSAGE);

                            return $this->redirect()->toUrl(MotTestUrlBuilderWeb::motTest($motTestNumber));
                        }
                        $data = [
                            'value' => (int)$validatedData['odometer'],
                            'unit' => $validatedData['unit'],
                            'resultType' => $readingResultType,
                        ];
                    } else {
                        $data = ['resultType' => $readingResultType];
                    }

                    $apiUrl = MotTestUrlBuilder::odometerReading($motTestNumber)->toString();
                    $this->getRestClient()->put($apiUrl, $data);
                } catch (RestApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages());
                }
            } else {
                $this->addErrorMessages(self::ODOMETER_FORM_ERROR_MESSAGE);
            }
        }

        return $this->redirect()->toUrl(MotTestUrlBuilderWeb::motTest($motTestNumber));
    }

    /**
     * Called by the display-test-summary.phtml pages from Test and Enforcement.
     *
     * If a reinspection was done at a different location then we must ensure that
     * the site id (if an existing one) os recorded or, if an offsite inspection was
     * performed, that a new site and comment record are recorded.
     *
     * @return \Zend\Http\Response
     */
    public function submitTestResultsAction()
    {
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', null);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            try {
                // Update the reinspection site location as necessary.
                try {
                    $this->updateSiteLocation($motTestNumber);
                    $this->updateOnePersonTest($motTestNumber);
                } catch (RestApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages());

                    return $this->redirect()->toRoute('mot-test/test-summary', ['motTestNumber' => $motTestNumber]);
                }
                
                $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();

                // TODO: Nobody ever checked that the results were submitted successfully!
                // Presumably the exception handler is the intended failure handler.
                try {
                    $this->getRestClient()->postJson($apiUrl, $data);
                } catch (OtpApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages()); // TODO contruct the message
                    return $this->redirect()->toUrl(MotTestUrlBuilderWeb::summary($motTestNumber));
                }

                return $this->redirect()->toUrl(MotTestUrlBuilderWeb::showResult($motTestNumber));
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        return $this->redirect()->toUrl(MotTestUrlBuilderWeb::summary($motTestNumber));
    }

    /**
     * Check for 'siteid' or 'location' in the POST data. If present then we need to modify the
     * mot_test record accordingly. ASSUME the request here is a POST action.
     *
     * @param $motTestNumber int the reinspection mot test identifier
     * @param bool $isRequired optional
     */
    private function updateSiteLocation($motTestNumber, $isRequired = false)
    {
        $data = $this->getRequest()->getPost()->toArray();

        $theSiteidEntry = isset($data['siteidentry']) ? $data['siteidentry'] : null;
        $theSiteid = isset($data['siteid']) ? $data['siteid'] : null;
        $theLocation = isset($data['location']) ? $data['location'] : null;
        $isNonMotTest = $this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER) &&
            isset($data['_non_mot_test']) && $data['_non_mot_test'] === $motTestNumber;

        if ($theLocation || $theSiteid || $theSiteidEntry || $isNonMotTest) {
            $apiUrl = MotTestUrlBuilder::motTest($motTestNumber)->toString();

            $this->getRestClient()->putJson(
                $apiUrl,
                [
                    'siteid' => ($theSiteid) ? $theSiteid : $theSiteidEntry,
                    'location' => $theLocation,
                    'operation' => 'updateSiteLocation',
                ]
            );
        } else if ($isRequired) {
            throw new ValidationException(new ValidationResult(false, [self::ERROR_NO_SITE_FOR_NON_MOT_TEST]));
        }
    }

    /**
     * Update MOT Test with One person test / re-test data.
     *
     * @param $motTestNumber int the reinspection mot test identifier
     */
    private function updateOnePersonTest($motTestNumber)
    {
        $data = $this->getRequest()->getPost()->toArray();
        $apiUrl = MotTestUrlBuilder::motTest($motTestNumber)->toString();

        if (isset($data['onePersonTest']) && isset($data['onePersonReInspection'])) {
            $this->getRestClient()
                ->putJson(
                    $apiUrl,
                    [
                        'onePersonTest' => $data['onePersonTest'],
                        'onePersonReInspection' => $data['onePersonReInspection'],
                        'operation' => 'updateOnePersonTest',
                    ]
                );
        }
    }

    public function displayCertificateSummaryAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::CERTIFICATE_READ);
        $number = $this->params()->fromRoute('motTestNumber');
        $duplicateCertSearchByVrm = $this->params()->fromQuery(DuplicateCertificateSearchType::SEARCH_TYPE_VRM);
        $duplicateCertSearchByVin = $this->params()->fromQuery(DuplicateCertificateSearchType::SEARCH_TYPE_VIN);

        $duplicateCertRouteSearchParams = $this->generateParamsForSearchBy(
            $duplicateCertSearchByVrm,
            $duplicateCertSearchByVin
        );
        $isDuplicateCertificate = $duplicateCertRouteSearchParams != null;

        /** @var MotTest $motTest */
        $motTest = $this->getMotTestFromApi($number);

        /** @var DvsaVehicle $vehicle */
        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion(
            $motTest->getVehicleId(),
            $motTest->getVehicleVersion()
        );

        /** @var DvsaVehicleViewModel $vehicleViewModel */
        $vehicleViewModel = new DvsaVehicleViewModel($vehicle);

        $this->odometerViewObject->setValue($motTest->getOdometerValue());
        $this->odometerViewObject->setUnit($motTest->getOdometerUnit());
        $this->odometerViewObject->setResultType($motTest->getOdometerResultType());

        $testType = $motTest->getTestTypeCode();

        $isDemo = MotTestType::isDemo($testType);
        $motTestEditAllowedDto = null;

        if ($isDuplicateCertificate) {
            /** @var MotTestDuplicateCertificateEditAllowedDto $motTestEditAllowedDto */
            $motTestEditAllowedDto = $this->duplicateCertificateApiResource->getEditAllowed(
                $number,
                $vehicle->getId()
            );
        }

        return (new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motDetails' => $motTest,
                'vehicleViewModel' => $vehicleViewModel,
                'odometerReading' => $this->odometerViewObject,
                'isDemo' => $isDemo,
                'brakeTestTypeCode2Name' => $this->getBrakeTestTypeCode2Name(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'isDuplicateCertificate' => $isDuplicateCertificate,
                'duplicateCertRouteSearchParams' => $duplicateCertRouteSearchParams,
                'motTestEditAllowed' => $motTestEditAllowedDto,
            ]
        ))->setTemplate('dvsa-mot-test/mot-test/display-test-summary');
    }

    private function generateParamsForSearchBy($vrm, $vin)
    {
        if ($vrm === null and $vin === null) {
            return null;
        } elseif ($vin === null) {
            return [
                DuplicateCertificateSearchType::SEARCH_TYPE_VRM => $vrm
            ];
        } else {
            return [
                DuplicateCertificateSearchType::SEARCH_TYPE_VIN => $vin
            ];
        }
    }

    /**
     * Called by the display-test-summary.phtml pages from Test and Enforcement.
     *
     * If a reinspection was done at a different location then we must ensure that
     * the site id (if an existing one) os recorded or, if an offsite inspection was
     * performed, that a new site and comment record are recorded.
     *
     * @return \Zend\Http\Response
     */
    public function displayTestSummaryAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $prgHelper = new PrgHelper($request);
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        };

        $otpErrorData = null;
        $errorMessage = null;
        $shortMessage = null;
        $data = null;
        $isDemo = null;

        $errorMessages = '';

        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages();
        if (!is_null($motTest)) {
            $this->assertCanConfirmMotTest($motTest);
        }

        if ($request->isPost()) {
            $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);

            $urlFinish = MotTestUrlBuilderWeb::showResult($motTestNumber);
            $prgHelper->setRedirectUrl($urlFinish->toString());

            $data = $request->getPost()->toArray();
            try {
                // Update the re-inspection site location as necessary.
                $this->updateSiteLocation($motTestNumber, $this->isNonMotTest($motTest));
                $this->updateOnePersonTest($motTestNumber);

                // Record client IP at this time
                $data['clientIp'] = $this->getClientIp();

                $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();
                $this->getRestClient()->post($apiUrl, $data);

                //  --  reset current site  --
                /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity $identity */
                $identity = $this->getIdentity();
                if ($this->getAuthorizationService()->isVehicleExaminer()) {
                    $identity->setCurrentVts();
                }

                return $this->redirect()->toUrl($urlFinish);

            } catch (OtpApplicationException $e) {
                $errorData = $e->getErrorData();

                if (isset($errorData['message'])) {
                    $this->addErrorMessages($errorData['message']);
                }

                if (isset($errorData['shortMessage'])) {
                    $this->addErrorMessages($errorData['shortMessage']);
                }

            } catch (RestApplicationException $e) {
                if ($e->containsError('This test has been aborted by DVSA and cannot be continued')
                    || $e->containsError('This test is completed and cannot be changed')) {
                    return $this->redirect()->toUrl($urlFinish);
                }
                $this->addErrorMessages($e->getDisplayMessages());
            } catch (ValidationException $e) {
                $this->addErrorMessages($e->getMessages());
            }
        }

        //  --  get odometer reading    --
        $this->odometerViewObject->setValue($motTest->getOdometerValue());
        $this->odometerViewObject->setUnit($motTest->getOdometerUnit());
        $this->odometerViewObject->setResultType($motTest->getOdometerResultType());

        /** @var DvsaVehicle $vehicleData */
        $vehicleData = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());

        /** @var DvsaVehicleViewModel $dvsaVehicleViewModel */
        $vehicle = new DvsaVehicleViewModel($vehicleData);

        /** @var string $testType */
        $testType = $motTest->getTestTypeCode();
        $isDemo = MotTestType::isDemo($testType);

        /** @var VehicleTestingStationDto $siteDto */
        $siteDto = null;

        if(!$isDemo && !MotTestType::isNonMotTypes($testType)) {
            $apiUrl = UrlBuilder::of()->vehicleTestingStation()->routeParam('id',$motTest->getSiteId())->toString();
            $response = $this->getRestClient()->get($apiUrl);
            $siteDto = $response['data'];
        }

        if ($this->isMysteryShopper($motTest)) {
            $mysteryShopperExpiryDate = (new MysteryShopperExpiryDateGenerator())->getCertificateExpiryDate();
            $mysteryShopperExpiryDate = DateTimeApiFormat::date($mysteryShopperExpiryDate);
            $motTest->updateExpiryDate($mysteryShopperExpiryDate);
        }

        return (new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motDetails' => $motTest,
                'vehicleViewModel' => $vehicle,
                'siteDto' => $siteDto,
                'id' => $this->params()->fromRoute('id', 0),
                'odometerReading' => $this->odometerViewObject,
                'isDemo' => $isDemo,
                'isNonMotTest' => $this->isNonMotTest($motTest),
                'otpErrorData' => $otpErrorData,
                'otpErrorMessage' => $errorMessage,
                'otpErrorShortMessage' => $shortMessage,
                'defaultValues' => $data,
                'prgHelper' => $prgHelper,
                'brakeTestTypeCode2Name' => $this->getBrakeTestTypeCode2Name(),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
                'errorMessages' => $errorMessages,
            ]
        ))->setTemplate('dvsa-mot-test/mot-test/display-test-summary');
    }

    private function isNonMotTest($motTest)
    {
        if (!$motTest instanceof MotTest) {
            return false;
        }

        /** @var string $testType */
        $testType = $motTest->getTestTypeCode();

        return
            $this->isFeatureEnabled(FeatureToggle::MYSTERY_SHOPPER) &&
            $this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM) &&
            MotTestType::isNonMotTypes($testType);
    }

    public function cancelMotTestAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        //  --  prevent error on double click   --
        $prgHelper = new PrgHelper($request);
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        };

        //  --  get mot test data   --
        $motTestNumber = (int)$this->params('motTestNumber', 0);

        /** @var MotTest $motTest */
        $motTest = $this->getMotTestFromApi($motTestNumber);

        //  --  check permission    --
        $this->getAuthorizationService()->assertGrantedAtSite(
            PermissionAtSite::MOT_TEST_ABORT_AT_SITE,
            $motTest->getSiteId()
        );

        $otpErrorData = null;
        $selectedReasonId = null;
        $cancelComment = null;
        $reasonsForCancel = $this->getCatalogService()->getData()['reasonsForCancel'];
        $reasonsForCancel = $this->removeNotDisplayableReasons($reasonsForCancel);

        $canAbandonVehicleTest = $this->abandonVehicleAssertion($motTest)->isGranted();
        if (!$canAbandonVehicleTest) {
            $reasonsForCancel = $this->removeAbandonedReasons($reasonsForCancel);
        }

        if ($request->isPost()) {
            $urlFinish = MotTestUrlBuilderWeb::cancelled($motTestNumber);
            $prgHelper->setRedirectUrl($urlFinish->toString());

            try {
                $data = $request->getPost()->toArray();
                $data['clientIp'] = $this->getClientIp();
                $selectedReasonId = ArrayUtils::tryGet($data, 'reasonForCancelId');
                $cancelComment = ArrayUtils::tryGet($data, 'cancelComment');

                $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();
                $this->getRestClient()->post($apiUrl, $data);

                return $this->redirect()->toUrl($urlFinish);
            } catch (OtpApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
                $otpErrorData = $e->getErrorData();
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        return new ViewModel(
            [
                'isMotContingency' => $this->getContingencySessionManager()->isMotContingency(),
                'motTestNumber' => $motTestNumber,
                'reasonsForCancel' => $reasonsForCancel,
                'selectedReasonId' => $selectedReasonId,
                'cancelComment' => $cancelComment,
                'canTestWithoutOtp' => $this->canTestWithoutOtp(),
                'otpErrorData' => $otpErrorData,
                'canAbandonVehicleTest' => $canAbandonVehicleTest,
                'prgHelper' => $prgHelper,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        );
    }

    /**
     * @param MotTest $motTest
     *
     * @return AbandonVehicleTestAssertion
     */
    private function abandonVehicleAssertion(MotTest $motTest)
    {
        /** @var PersonDto $tester */
        $tester = $motTest->getTester();
        $testType = $motTest->getTestTypeCode();

        $vtsId = $motTest->getSiteId();

        $testerId = $tester->getId();
        $testCode = $testType;
        $abandonTestAssertion = new AbandonVehicleTestAssertion($this->getIdentity(), $this->getAuthorizationService());
        $abandonTestAssertion->setTesterId($testerId)->setVtsId($vtsId)->setMotTestTypeCode($testCode);

        return $abandonTestAssertion;
    }

    /**
     * @param ReasonForCancelDto[] $reasons
     *
     * @return ReasonForCancelDto[]
     */
    private function removeAbandonedReasons(array $reasons)
    {
        return ArrayUtils::filter(
            $reasons,
            function (ReasonForCancelDto $reason) {
                if ($reason->getAbandoned()) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @param ReasonForCancelDto[] $reasons
     *
     * @return ReasonForCancelDto[]
     */
    private function removeNotDisplayableReasons(array $reasons)
    {
        return ArrayUtils::filter(
            $reasons, function (ReasonForCancelDto $reason) {
            return $reason->getIsDisplayable();
        }
        );
    }

    public function cancelledMotTestAction()
    {
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages();

        $testDocument = self::TEST_DOCUMENT_VT32;
        if (isset($motTest) && $motTest->getStatus() === MotTestStatusName::ABANDONED) {
            $testDocument = self::TEST_DOCUMENT_VT30;
        }

        return new ViewModel(
            [
                'motTest' => $motTest,
                'testDocument' => $testDocument,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        );
    }

    public function deleteReasonForRejectionAction()
    {
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
        $rfrId = (int)$this->params()->fromRoute('rfr-id', 0);
        $redirectUrl = MotTestUrlBuilderWeb::motTest($motTestNumber);

        try {
            $apiUrl = MotTestUrlBuilder::reasonForRejection($motTestNumber, $rfrId)->toString();
            $this->getRestClient()->delete($apiUrl);
        } catch (NotFoundException $e) {
            /* VM-4453 - Catch double click of remove RFR */
            return $this->redirect()->toUrl($redirectUrl);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return $this->redirect()->toUrl($redirectUrl);
    }

    /**
     * Called to retrieve a PDF from the document service (and Jasper), and returns
     * the binary with content-type header.
     * Relies on an MOT ID being passed in the URL, which resolves to a document ID.
     *
     * @throws RestApplicationException
     * @throws \Exception
     *
     * @return Response
     */
    public function retrievePdfAction()
    {
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
        $isDuplicate = $this->params('isDuplicate');

        $certificateUrl = ReportUrlBuilder::printCertificate($motTestNumber, ($isDuplicate ? 'dup' : null));

        $result = $this->getRestClient()->getPdf($certificateUrl); // @todo - add some pdf parsing checks in client

        $response = new Response();
        $response->setContent($result);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');

        return $response;
    }

    public function testResultAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', 0);

        /**
         * @var MotTest
         */
        $motDetails = $this->tryGetMotTestOrAddErrorMessages();

        /** @var DvsaVehicle $vehicle */
        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motDetails->getVehicleId(), $motDetails->getVehicleVersion());

        $this->addTestNumberAndTypeToGtmDataLayer($motTestNumber, $motDetails->getTestTypeCode());

        $params = [
            'motDetails' => $motDetails,
            'motTestNumber' => $motTestNumber,
            'isDuplicate' => false,
        ];

        $this->eventManager->trigger(MotEvents::MOT_TEST_COMPLETED, $this, $params);

        $this->layout()->setVariable('hideChangeSiteLink', true);

        $model = new MotPrintModel(
            [
                'motDetails' => $motDetails,
                'motTestNumber' => $motTestNumber,
                'isDuplicate' => false,
                'vehicle' => $vehicle
            ]
        );

        $model->setTemplate('dvsa-mot-test/mot-test/print-test-result');

        return $model;
    }

    public function printDuplicateCertificateResultAction()
    {
        $this->getAuthorizationService()->assertGranted(PermissionInSystem::CERTIFICATE_READ);

        $motTestNumber = $this->params()->fromRoute('motTestNumber', 0);

        $motDetails = $this->tryGetMotTestOrAddErrorMessages();

        $this->layout()->setVariable('hideChangeSiteLink', true);

        $viewModel = new MotPrintModel(
            [
                'motDetails' => $motDetails,
                'motTestNumber' => $motTestNumber,
                'isDuplicate' => true,
            ]
        );

        $viewModel->setTemplate('dvsa-mot-test/mot-test/print-test-result');

        return $viewModel;
    }

    protected function assertCanAbortTest(MotTest $motTest)
    {
        if (!$this->canAbortTest($motTest)) {
            throw new UnauthenticatedException();
        }
    }

    protected function canAbortTest(MotTest $motTest)
    {
        /** @var MotTest $testType */
        $testType = $motTest->getTestTypeCode();

        if (MotTestType::isReinspection($testType)) {
            return $this->authorisationService->isGranted(PermissionInSystem::VE_MOT_TEST_ABORT);
        }

        return $this->canAbortTestAtSite($motTest->getSiteId());
    }

    protected function assertCanAbortTestAtSite(MotTest $motTest)
    {
        $siteId = $motTest->getSiteId();
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::MOT_TEST_ABORT_AT_SITE, $siteId);
    }

    protected function assertCanViewTestInProgress(MotTest $motTest)
    {
        $siteId = $motTest->getSiteId();
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, $siteId);
    }

    protected function canAbortTestAtSite($siteId)
    {
        return $this->authorisationService->isGrantedAtSite(PermissionAtSite::MOT_TEST_ABORT_AT_SITE, $siteId);
    }

    /**
     * Respond to /mot-test/:test_id/review-to-abort
     * Renders the dvsa-mot-test/mot-test/short-summary.phtml.
     *
     * @return \Zend\Http\Response
     */
    public function shortSummaryAction()
    {
        $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);

        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());

        $this->assertCanViewTestInProgress($motTest);

        $canAbortTest = $this->canAbortTest($motTest);

        return new ViewModel(
            [
                'motTest' => $motTest,
                'vehicle' => $vehicle,
                'canAbortTest' => $canAbortTest,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        );
    }

    /**
     * Respond to /mot-test/:test_id/reason-for-aborting
     * Renders the dvsa-mot-test/mot-test/reason-for-aborting-mot-test.phtml.
     *
     * @return \Zend\Http\Response
     */
    public function reasonForAbortingMotTestAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages();

        $this->assertCanAbortTest($motTest);

        $selectedReasonId = null;

        if ($request->isPost()) {
            $motTestNumber = null;

            try {
                $data = $request->getPost()->toArray();

                $motTestNumber = $this->params()->fromRoute('motTestNumber');
                $selectedReasonId = ArrayUtils::tryGet($data, 'reasonForCancelId');
                $data['status'] = MotTestStatusName::ABORTED;
                $data['clientIp'] = $this->getClientIp();

                $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();
                $this->getRestClient()->post($apiUrl, $data);

                return $this->redirect()->toUrl(MotTestUrlBuilderWeb::abortSuccess($motTestNumber));
            } catch (RestApplicationException $e) {
                $errorMessageOnNonActiveTests = InvalidTestStatus::getMessage(
                    MotTestStatusName::ABORTED
                );

                foreach ($e->getErrors() as $error) {
                    if ($errorMessageOnNonActiveTests === ArrayUtils::tryGet($error, 'message')) {
                        return $this->redirect()->toUrl(MotTestUrlBuilderWeb::abortFail($motTestNumber));
                    }
                }

                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        $reasonsForCancel = array_filter(
            $this->getCatalogByName('reasonsForCancel'),
            function (ReasonForCancelDto $reason) {
                return !$reason->getAbandoned() && $reason->getIsDisplayable();
            }
        );

        return (new ViewModel(
            [
                'motTest' => $motTest,
                'reasonsForCancel' => $reasonsForCancel,
                'selectedReasonId' => $selectedReasonId,
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        ));
    }

    /**
     * Respond to /mot-test/:test_id/abort-success
     *          & /mot-test/:test_id/abort-fail
     * Renders the dvsa-mot-test/mot-test/aborted-mot-test.phtml.
     *
     * @return \Zend\Http\Response
     */
    public function abortedMotTestAction()
    {
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages();

        $this->assertCanAbortTest($motTest);

        return (new ViewModel(
            [
                'motTest' => $motTest,
                'success' => $this->params()->fromRoute('success', false),
                'motTestTitleViewModel' => (new MotTestTitleModel()),
            ]
        ));
    }

    /**
     * @return bool
     */
    private function canTestWithoutOtp()
    {
        return $this->getAuthorizationService()->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP);
    }

    /**
     * @param $motTestNumber
     *
     * @return Response redirection
     */
    private function redirectToSelectLocation($motTestNumber)
    {
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $route = $routeMatch->getMatchedRouteName();
        $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
        $container->persistConfig(['route' => $route, 'params' => ['motTestNumber' => $motTestNumber]]);

        return $this->redirect()->toRoute('location-select');
    }

    private function assertUserOwnsTheMotTest(MotTest $motTest)
    {
        $tester = $motTest->getTester();

        if ($this->getIdentity()->getUserId() !== $tester->getId()) {
            throw new UnauthorisedException(
                'This test was started by another user and you are not allowed to confirm its result'
            );
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @throws UnauthorisedException
     */
    private function assertCanConfirmMotTest(MotTest $motTest)
    {
        /** @var string $testType */
        $testType = $motTest->getTestTypeCode();

        if (MotTestType::isDemo($testType)) {
            return;
        }

        $this->getAuthorizationService()->assertGranted(PermissionInSystem::MOT_TEST_CONFIRM);
        $this->assertUserOwnsTheMotTest($motTest);

        if (MotTestType::isNonMotTypes($testType)) {
            return;
        }

        $this->getAuthorizationService()->assertGrantedAtSite(
            PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE,
            $motTest->getSiteId()
        );
    }

    /**
     * @return ContingencySessionManager
     */
    private function getContingencySessionManager()
    {
        return $this->serviceLocator->get(ContingencySessionManager::class);
    }

    private function getBrakeTestTypeCode2Name()
    {
        /** @var CatalogService $catalogService */
        $catalogService = $this->getCatalogService();

        $brakeTestTypeCode2Name = [];
        foreach ($catalogService->getData()['brakeTestType'] as $breakTestType) {
            $brakeTestTypeCode2Name[$breakTestType['code']] = $breakTestType['name'];
        }

        return $brakeTestTypeCode2Name;
    }

    /**
     * Extract the available IP address, normally we expect to find this in a
     * custom header, X-=Forwarded-For. If it is not present use a default.
     *
     * @return string
     */
    private function getClientIp()
    {
        $ipAddress = Network::DEFAULT_CLIENT_IP;
        $headers = $this->request->getHeaders();

        if ($headers->has('X-Forwarded-For')) {
            $header = $headers->get('X-Forwarded-For');
            $ips = explode(',', $header->getFieldValue());
            if (!empty($ips) && !empty($ips[0])) {
                $ipAddress = $ips[0];
            }
        }

        return $ipAddress;
    }

    /**
     * @param string $template
     * @param array $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * @param string $motTestNumber
     * @param string $testTypeCode
     */
    private function addTestNumberAndTypeToGtmDataLayer($motTestNumber, $testTypeCode)
    {
        $this->gtmDataLayer([
            'testId' => $motTestNumber,
            'testType' => $testTypeCode,
        ]);
    }

    /**
     * @param MotTest $data
     *
     * @return bool
     */
    private function isMysteryShopper(MotTest $data)
    {
        if (!$this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER)) {
            return false;
        };

        return ($data->getTestTypeCode() !== null)
        && ($data->getTestTypeCode() === MotTestTypeCode::MYSTERY_SHOPPER);
    }
}
