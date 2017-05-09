<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Controller;

use Application\Helper\PrgHelper;
use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\Make;
use Dvsa\Mot\ApiClient\Resource\Item\Model;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Resource\Item\Tester;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilderWeb;
use DvsaCommon\Utility\AddressUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Form\VrmUpdateForm;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTest\Model\OdometerUpdate;
use DvsaMotTest\View\Model\MotPrintModel;
use DvsaMotTest\View\ReplacementMakeViewModel;
use DvsaMotTest\View\ReplacementSiteViewModel;
use DvsaMotTest\View\ReplacementVehicleViewModel;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
use Vehicle\Helper\ColoursContainer;
use Vehicle\Service\VehicleCatalogService;
use Zend\View\Model\ViewModel;

/**
 * Class ReplacementCertificateController.
 */
class ReplacementCertificateController extends AbstractDvsaMotTestController
{
    const TEMPLATE_REPLACEMENT_CERTIFICATE = 'dvsa-mot-test/mot-test/replacement-certificate';
    const TEMPLATE_REPLACEMENT_CERTIFICATE_FINISH = 'dvsa-mot-test/mot-test/replacement-certificate-finish';
    const TEMPLATE_REPLACEMENT_CERTIFICATE_SUMMARY = 'dvsa-mot-test/mot-test/replacement-certificate-summary';
    const TEMPLATE_REPLACEMENT_CERTIFICATE_OTHER_VEHICLE = 'dvsa-mot-test/mot-test/replacement-certificate-other-vehicle';

    const SITE_NOT_FOUND = 'Site number not found';
    const INTACT_CERT_DETAIL = 'Nothing has changed for the certificate';

    const ACTION_UPDATE_ODOMETER = 'updateOdometer';

    const MAKE_MODEL_OTHER_VALUE = 'other';
    const MODEL_VALUE_EMPTY = 'Model not found';
    const ACTION_UPDATE_VRM = 'updateVrm';

    /** @var VehicleCatalogService */
    private $vehicleCatalogService;

    /**
     * @var OdometerReadingViewObject
     */
    private $odometerViewObject;

    /**
     * ReplacementCertificateController constructor.
     *
     * @param VehicleCatalogService     $vehicleCatalogService
     * @param OdometerReadingViewObject $odometerViewObject
     */
    public function __construct(
        VehicleCatalogService $vehicleCatalogService,
        OdometerReadingViewObject $odometerViewObject
    ) {
        $this->vehicleCatalogService = $vehicleCatalogService;
        $this->odometerViewObject = $odometerViewObject;
    }

    /**
     * @return null|\Zend\Http\Response|ViewModel
     */
    public function replacementCertificateAction()
    {
        $this->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);

        $id = $this->params()->fromRoute('id', null);
        $makeId = $this->params()->fromRoute('makeId', null);
        $motTestNumber = $this->params()->fromRoute('motTestNumber', 0);

        if ($this->getRequest()->isPost()) {
            if ($id === null) {
                return $this->createDraft($motTestNumber);
            }

            return $this->updateDraft($id, $motTestNumber);
        }

        return $this->showDraft($id, $motTestNumber, $makeId);
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function reviewAction()
    {
        $this->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);

        $id = $this->params()->fromRoute('id', 0);
        $motTestNumber = $this->params()->fromRoute('motTestNumber', 0);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        //  --  double post check  --
        $prgHelper = new PrgHelper($request);
        if ($prgHelper->isRepeatPost()) {
            return $this->redirect()->toUrl($prgHelper->getRedirectUrl());
        }

        //  --  get draft data from Api --
        $draft = $this->getDraftData($id, $motTestNumber);
        $motTestNumber = ArrayUtils::tryGet($draft, 'motTestNumber');

        //  --  get mottest data from Api --
        /** @var MotTest $motTest */
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);
        /** @var DvsaVehicle $vehicle */
        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());
        $otpErrorData = null;

        if ($request->isPost()) {
            $urlFinish = UrlBuilderWeb::replacementCertificateFinish($motTestNumber);

            $prgHelper->setRedirectUrl($urlFinish->toString());

            try {
                if (!$this->hasAdminRights()) {
                    $testerDto = $motTest->getTester();
                    $isOriginalTester = $this->getIdentity()->getUserId() === $testerDto->getId();
                    if (!$isOriginalTester) {
                        $diffTesterReasonCode = $this->params()->fromPost('reasonForDifferentTester');
                        $data = ['reasonForDifferentTester' => $diffTesterReasonCode];
                        $this->getRestClient()->put(
                            UrlBuilder::replacementCertificateDraft($id, $motTestNumber),
                            $data
                        );
                    }
                }

                $otp = $this->params()->fromPost('oneTimePassword');
                $data = ['oneTimePassword' => $otp];
                $this->getRestClient()->post(
                    UrlBuilder::replacementCertificateDraftApply($id, $motTestNumber),
                    $data
                );

                return $this->redirect()->toUrl($urlFinish);
            } catch (OtpApplicationException $e) {
                $errorData = $e->getErrorData();

                if (isset($errorData['attempts']) && $errorData['attempts']['total'] > 0) {
                    if (isset($errorData['message'])) {
                        $message = $errorData['message'];
                        $this->addErrorMessages($message);
                    }
                }

                $otpErrorData = $e->getErrorData();
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        /** @var ColourDto $primaryColour */
        $primaryColour = new ColourDto();
        $primaryColour->setName($vehicle->getColour()->getName());

        $secondaryColourDraft = $draft['secondaryColour'];
        /** @var ColourDto $secondaryColour */
        $secondaryColour = new ColourDto();
        $secondaryColour->setName($vehicle->getColourSecondary()->getName());

        $secondaryColour
            ->setName(ArrayUtils::tryGet($secondaryColourDraft, 'name'))
            ->setCode(ArrayUtils::tryGet($secondaryColourDraft, 'code'));

        $vehicleViewModel = new DvsaVehicleViewModel($vehicle);

        if ($this->hasAdminRights()) {
            $countryOfRegistration = new CountryDto();

            $vehicleViewModel->setVin($draft['vin']);
            $vehicleViewModel->setRegistration($draft['vrm']);

            $make = new \stdClass();
            $make->id = $draft['make']['id'];
            $make->name = $draft['make']['name'];
            $vehicleViewModel->setMake(new Make($make));

            $model = new \stdClass();
            $model->id = $draft['model']['id'];
            $model->name = $draft['model']['name'];
            $vehicleViewModel->setModel(new Model($model));

            $countryOfRegistration->setName($draft['countryOfRegistration']['name']);

            $draft['vts']['address'] = AddressUtils::stringify($draft['vts']['address']);
            //$motTest->setVehicleTestingStation($draft['vts']);
        }

        $this->odometerViewObject->setValue($draft['odometerReading']['value']);
        $this->odometerViewObject->setUnit($draft['odometerReading']['unit']);
        $this->odometerViewObject->setResultType($draft['odometerReading']['resultType']);

        /** @var Tester $tester */
        $tester = $motTest->getTester();
        $isOriginalTester = $this->getIdentity()->getUserId() === $tester->getId();

        $differentTesterReasons = [];
        if (!$isOriginalTester) {
            $reasons = $this->getRestClient()->get('cert-change-diff-tester-reason')['data'];

            foreach ($reasons as $r) {
                $differentTesterReasons[$r['code']] = $r['description'];
            }
        }

        $viewModel = new ViewModel(
            [
                'motTest' => $motTest,
                'odometerReading' => $this->odometerViewObject,
                'vehicleViewModel' => $vehicleViewModel,
                'isOriginalTester' => $isOriginalTester,
                'expiryDate' => $draft['expiryDate'],
                'differentTesterReasons' => $differentTesterReasons,
                'draftId' => $id,
                'isAdmin' => $this->hasAdminRights(),
                'otpErrorData' => $otpErrorData,
                'prgHelper' => $prgHelper,
            ]
        );
        $viewModel->setTemplate(self::TEMPLATE_REPLACEMENT_CERTIFICATE_SUMMARY);

        return $viewModel;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function finishAction()
    {
        $this->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);

        $motTestNumber = $this->params('motTestNumber');
        $motTest = $this->tryGetMotTestOrAddErrorMessages($motTestNumber);

        $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());

        $modelPrintViewModel = new MotPrintModel(
            [
                'motDetails' => $motTest,
                'motTestNumber' => $motTestNumber,
                'vehicle' => $vehicle,
            ]
        );

        $modelPrintViewModel->setTemplate(self::TEMPLATE_REPLACEMENT_CERTIFICATE_FINISH);

        return $modelPrintViewModel;
    }

    public function otherVehicleAction()
    {
        $this->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS);

        $id = $this->params()->fromRoute('id', null);
        $makeCode = $this->params()->fromRoute('makeCode', null);
        $motTestNumber = $this->params()->fromRoute('motTestNumber', 0);

        $draft = $this->getDraftData($id, $motTestNumber);

        if ($this->getRequest()->isPost()) {
            if ($id === null) {
                return $this->createDraft($this->params()->fromPost('motTestNumber'));
            }

            return $this->updateDraft($id, $motTestNumber);
        }

        $viewData = $this->buildViewData($draft, $makeCode);

        $viewData = array_merge(
            $viewData,
            [
                'isAdmin' => $this->hasAdminRights(),
                'isTester' => $this->hasTesterRights(),
                'id' => $id,
                'motTestNumber' => $motTestNumber,
            ]
        );

        $this->layout('layout/layout-govuk.phtml');

        return (new ViewModel($viewData))->setTemplate(self::TEMPLATE_REPLACEMENT_CERTIFICATE_OTHER_VEHICLE);
    }

    /**
     * @param $motTestNumber
     *
     * @return \Zend\Http\Response
     */
    private function createDraft($motTestNumber)
    {
        $draftId = $this->getRestClient()->post(
            UrlBuilder::replacementCertificateDraft(null, $motTestNumber),
            ['motTestNumber' => $motTestNumber]
        )['data']['id'];

        return $this->redirectToDraft($draftId, $motTestNumber);
    }

    /**
     * @param string $id
     * @param string $motTestNumber
     *
     * @return null|\Zend\Http\Response|ViewModel
     */
    private function updateDraft($id, $motTestNumber)
    {
        $action = $this->params()->fromPost('action');
        $result = $this->getUpdateData($action);

        // if input is not empty, update the draft
        if (!empty($result)) {
            if ($action === 'updateMake') {
                $make = $result['make'];
                if ($make === self::MAKE_MODEL_OTHER_VALUE) {
                    return $this->redirect()->toRoute(
                        'mot-test/replacement-certificate/other-vehicle',
                        ['id' => $id, 'motTestNumber' => $motTestNumber]
                    );
                } else {
                    return $this->redirect()->toRoute(
                        'mot-test/replacement-certificate/select-model',
                        ['id' => $id, 'makeId' => $result['make'], 'motTestNumber' => $motTestNumber]
                    );
                }
            }

            if ($action === 'updateModel') {
                $model = $result['model'];
                $make = $result['make'];

                if (empty($make)) {
                    $make = self::MAKE_MODEL_OTHER_VALUE;
                } else {
                    $make = $result['make'];
                }

                if ($model === self::MAKE_MODEL_OTHER_VALUE) {
                    return $this->redirect()->toRoute(
                        'mot-test/replacement-certificate/other-vehicle',
                        ['id' => $id, 'makeCode' => $make, 'motTestNumber' => $motTestNumber]
                    );
                }
            }

            if ($action === self::ACTION_UPDATE_VRM) {
                $vrmForm = new VrmUpdateForm();
                $vrmForm->setData($result);

                if (!$vrmForm->isValid()) {
                    $this->addErrorMessages($vrmForm->getMessages(VrmUpdateForm::FIELD_VRM));

                    return $this->redirect()->toUrl(UrlBuilderWeb::replacementCertificate($id, $motTestNumber));
                }
            }

            try {
                if (self::ACTION_UPDATE_ODOMETER === $action) {
                    $odometerReadingParams = $result['odometerReading'];

                    $odometerForm = $this->getForm(new OdometerUpdate());
                    $odometerForm->setData($odometerReadingParams);

                    if (!$odometerForm->isValid()) {
                        $this->addErrorMessages(MotTestController::ODOMETER_FORM_ERROR_MESSAGE);

                        return $this->redirect()->toUrl(UrlBuilderWeb::replacementCertificate($id, $motTestNumber));
                    }

                    if ($odometerReadingParams['resultType'] !== OdometerReadingResultType::OK) {
                        unset($result['odometerReading']['odometer'], $result['odometerReading']['unit']);
                    } else {
                        $result['odometerReading']['value'] = (int) $result['odometerReading']['odometer'];
                        unset($result['odometerReading']['odometer']);
                    }
                }

                $this->getRestClient()->put(
                    UrlBuilder::replacementCertificateDraft($id, $motTestNumber),
                    $result
                );
            } catch (ValidationException $ve) {
                $this->addErrorMessages($ve->getDisplayMessages());

                return $this->redirect()->refresh();
            } catch (NotFoundException $e) {
                $this->addErrorMessages(self::SITE_NOT_FOUND);

                return $this->redirect()->refresh();
            }
        }

        $apiUrl = UrlBuilder::replacementCertificateDraftDiff($id, $motTestNumber);
        $diff = $this->getRestClient()->get($apiUrl)['data'];

        if (empty($diff)) {
            $this->addErrorMessages(self::INTACT_CERT_DETAIL);
        } elseif ($action === 'updateCertificate') {
            return $this->redirect()->toUrl(UrlBuilderWeb::replacementCertificateSummary($id, $motTestNumber));
        }

        return $this->redirectToDraft($id, $motTestNumber);
    }

    /**
     * @param string $updateAction
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function getUpdateData($updateAction)
    {
        $post = $this->params()->fromPost();
        switch ($updateAction) {
            case 'updateCertificate':
                return $this->hasAdminRights() ? ['reasonForReplacement' => $post['reasonForReplacement']] : [];
            case 'updateVts':
                return ['vtsSiteNumber' => $post['vts']];
            case 'updateVin':
                return ['vin' => $post['vin']];
            case self::ACTION_UPDATE_VRM:
                return ['vrm' => mb_strtoupper(preg_replace('/\s+/', '', $post['vrm']))];
            case 'updateColours':
                return [
                    'primaryColour' => $post['primaryColour'],
                    'secondaryColour' => $post['secondaryColour'] ?: null,
                ];
            case 'updateMake':
                return [
                    'make' => $post['make'],
                ];
            case 'updateModel':
                return [
                    'make' => $post['make'],
                    'model' => $post['model'],
                ];
            case 'updateCustomMakeModel':
                return [
                    'customMake' => $post['make'],
                    'customModel' => $post['model'],
                ];
            case 'updateMakeCustomModel':
                return [
                    'make' => $post['make'],
                    'customModel' => $post['model'],
                ];
            case self::ACTION_UPDATE_ODOMETER:
                list($value, $unit, $resultType)
                    = [$post['odometerValue'], $post['odometerUnit'], $post['odometerResultType']];

                if ($resultType !== OdometerReadingResultType::OK) {
                    $value = null;
                }

                if ($value === null) {
                    $value = (int) 0;
                }

                return [
                    'odometerReading' => [
                        'odometer' => $value,
                        'unit' => $unit,
                        'resultType' => $resultType,
                    ],
                ];
            case 'updateCor':
                return ['countryOfRegistration' => (int) $post['cor']];
            case 'updateExpiryDate':
                $year = $post['expiryDateYear'];
                $year = str_pad($year, 2, '0', STR_PAD_LEFT);

                $month = $post['expiryDateMonth'];
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);

                $day = $post['expiryDateDay'];
                $day = str_pad($day, 2, '0', STR_PAD_LEFT);

                return [
                    'expiryDate' => $year.'-'.$month.'-'.$day,
                ];
            default:
                throw new \InvalidArgumentException("$updateAction action is invalid");
        }
    }

    /**
     * @param string $id
     * @param string $motTestNumber
     *
     * @return mixed
     */
    private function getDraftData($id, $motTestNumber)
    {
        $apiPath = UrlBuilder::replacementCertificateDraft($id, $motTestNumber);

        return ArrayUtils::tryGet($this->getRestClient()->get((string) $apiPath), 'data');
    }

    /**
     * @param string $id
     * @param string $motTestNumber
     * @param int    $makeId
     *
     * @return ViewModel
     */
    private function showDraft($id, $motTestNumber, $makeId = null)
    {
        $draft = $this->getDraftData($id, $motTestNumber);
        $viewData = $this->buildViewData($draft, $makeId);

        $viewData = array_merge(
            $viewData,
            [
                'isAdmin' => $this->hasAdminRights(),
                'isTester' => $this->hasTesterRights(),
            ]
        );

        return (new ViewModel($viewData))->setTemplate(self::TEMPLATE_REPLACEMENT_CERTIFICATE);
    }

    /**
     * @param int $makeId
     *
     * @return array
     */
    private function getStaticData($makeId)
    {
        /** @var CatalogService $catalogService */
        $catalogService = $this->getCatalogService();
        $colourMap = $catalogService->getColours();
        asort($colourMap);

        return [
            'colours' => new ColoursContainer($catalogService->getColours()),
            'countryOfRegistrationList' => $catalogService->getCountriesOfRegistration(),
            'makeList' => $this->vehicleCatalogService->findMake(),
            'modelList' => $this->vehicleCatalogService->findModel(false, $makeId),
        ];
    }

    /**
     * @param string $id
     * @param string $motTestNumber
     *
     * @return \Zend\Http\Response
     */
    private function redirectToDraft($id, $motTestNumber)
    {
        return $this->redirect()->toUrl(UrlBuilderWeb::replacementCertificate($id, $motTestNumber));
    }

    /**
     * @param $draft
     *
     * @return OdometerReadingViewObject
     */
    private function buildOdometerReadingViewObject($draft)
    {
        $this->odometerViewObject->setValue($draft['odometerReading']['value']);
        $this->odometerViewObject->setUnit($draft['odometerReading']['unit']);
        $this->odometerViewObject->setResultType($draft['odometerReading']['resultType']);

        $apiUrl = MotTestUrlBuilder::odometerReadingModifyCheck($draft['motTestNumber'])->toString();

        if (!$this->hasAdminRights()) {
            $apiResult = $this->getRestClient()->get($apiUrl);

            $odometerModifiable = $apiResult['data']['modifiable'];
            $this->odometerViewObject->setModifiable($odometerModifiable);
        }

        return $this->odometerViewObject;
    }

    /**
     * @param array $draftData
     * @param int   $makeId
     *
     * @return array
     *
     * @throws \Exception
     */
    private function buildViewData($draftData, $makeId)
    {
        $readingVO = $this->buildOdometerReadingViewObject($draftData);

        $motTestNumber = ArrayUtils::tryGet($draftData, 'motTestNumber');

        $vehicleViewModel = new ReplacementVehicleViewModel($draftData);

        $motTest = $this->tryGetMotTestOrAddErrorMessages();

        $dvsaVehicleViewModel =
            new DvsaVehicleViewModel($this->getVehicleServiceClient()
                ->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion()));

        if ($makeId) {
            $vehicleViewModel->getMake()->setId((int) $makeId);
            $vehicleViewModel->setDisplayModelBody(true);
        }

        if ($makeId === self::MAKE_MODEL_OTHER_VALUE) {
            $makeId = null;
        } else {
            $makeId = $vehicleViewModel->getMake()->getId();
        }

        $staticData = $this->getStaticData($makeId);

        $selectedMake = $this->getSelectedMakeOption($makeId, $staticData['makeList']);

        if ($selectedMake) {
            $vehicleViewModel->setMake($selectedMake);
        }

        $viewData = array_merge(
            [
                'odometerReading' => $readingVO,
                'motTestNumber' => $motTestNumber,
                'motTest' => $motTest,
                'dvsaVehicleViewModel' => $dvsaVehicleViewModel,
                'vts' => new ReplacementSiteViewModel($draftData),
                'vehicle' => $vehicleViewModel,
            ],
            $staticData
        );

        return $viewData;
    }

    /**
     * @param int   $makeId
     * @param array $makeList
     *
     * @return bool|ReplacementMakeViewModel
     *
     * @throws \Exception
     */
    private function getSelectedMakeOption($makeId, array $makeList)
    {
        if (!$makeId) {
            return false;
        }

        foreach ($makeList as $make) {
            if ($makeId === $make['id']) {
                $modelView = new ReplacementMakeViewModel($make);

                return $modelView;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function hasAdminRights()
    {
        return $this->getAuthorizationService()->isGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS);
    }

    /**
     * @return bool
     */
    private function hasTesterRights()
    {
        return $this->getAuthorizationService()->isGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);
    }
}
