<?php

namespace DvsaMotTest\Controller;

use Application\Service\CatalogService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\Assertion\RefuseToTestAssertion;
use DvsaCommon\Dto\Common\ReasonForRefusalDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\UrlBuilder\ReportUrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Constants\VehicleSearchSource;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class RefuseToTestController extends AbstractDvsaMotTestController
{
    const ROUTE_REFUSE_TO_TEST_REASON = 'refuse-to-test/reason';
    const ROUTE_REFUSE_TO_TEST_PRINT = 'refuse-to-test/print';

    const PAGE_TITLE = 'Refuse to test';
    const PAGE_SUBTITLE = 'MOT testing';

    /** @var ParamObfuscator $paramObfuscator */
    private $paramObfuscator;

    /**
     * @var \Dvsa\Mot\ApiClient\Service\VehicleService
     */
    private $vehicleService;

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var MotFrontendIdentityInterface
     */
    private $identityProvider;

    /**
     * RefuseToTestController constructor.
     * @param ParamObfuscator $paramObfuscator
     * @param Client $restClient
     * @param VehicleService $vehicleService
     * @param MotFrontendAuthorisationServiceInterface $authorisationService
     * @param CatalogService $catalogService
     * @param \Zend\Session\Container $motSession
     * @param MotFrontendIdentityProviderInterface $identityProvider
     */
    public function __construct(
        ParamObfuscator $paramObfuscator,
        Client $restClient,
        VehicleService $vehicleService,
        MotFrontendAuthorisationServiceInterface $authorisationService,
        CatalogService $catalogService,
        \Zend\Session\Container $motSession,
        MotFrontendIdentityProviderInterface $identityProvider
        )
    {
        $this->paramObfuscator = $paramObfuscator;
        $this->restClient = $restClient;
        $this->vehicleService = $vehicleService;
        $this->authorisationService = $authorisationService;
        $this->catalogService = $catalogService;
        $this->motSession = $motSession;
        $this->identityProvider = $identityProvider;
    }

    public function refuseToTestReasonAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $source = $request->getQuery('source');
        $noReg = $request->getQuery('no-reg', true);

        $obfuscatedVehicleId = (string) $this->params()->fromRoute('id', 0);
        $vehicleId = $this->paramObfuscator->deobfuscateEntry(
            ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId, false
        );

        $testTypeCode = $this->params()->fromRoute('testTypeCode', MotTestTypeCode::NORMAL_TEST);
        $this->setHeadTitle('Refuse to test');

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();

            if (array_key_exists('refusal', $postData) === false) {
                $this->addErrorMessages('Please select reason for refusal');

                return $this->createViewModel($source, $noReg, $vehicleId);
            }

            $selectedReason = ArrayUtils::get($postData, 'refusal');

            $currentVts = $this->identityProvider->getIdentity()->getCurrentVts();

            $data = [
                'vehicleId' => $vehicleId,
                'rfrId' => $selectedReason,
                'siteId' => $currentVts ? $currentVts->getVtsId() : '',
            ];

            $this->assertRefuseToTest($data['siteId']);

            try {
                $apiUrl = MotTestUrlBuilder::refusal();
                $result = $this->restClient->post($apiUrl, $data);

                $this->motSession->offsetSet('mot-test-refusal-'.$obfuscatedVehicleId, $result);

                $this->redirect()->toUrl(MotTestUrlBuilderWeb::refuseSummary($testTypeCode, $obfuscatedVehicleId));
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        return $this->createViewModel($source, $noReg, $vehicleId);
    }

    public function refuseToTestSummaryAction()
    {
        $obfuscatedVehicleId = (string) $this->params()->fromRoute('id', 0);

        $testTypeCode = $this->params()->fromRoute('testTypeCode', MotTestTypeCode::NORMAL_TEST);

        $result = $this->motSession->offsetGet('mot-test-refusal-'.$obfuscatedVehicleId);

        if (empty($result)) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        $title = AbstractDvsaMotTestController::getTestName($testTypeCode).' refused';

        $viewVariables = [
            'title' => $title,
        ];

        return new ViewModel($viewVariables);
    }

    public function refuseToTestPrintAction()
    {
        $vehicleId = (string) $this->params()->fromRoute('id', 0);
        $result = $this->motSession->offsetGet('mot-test-refusal-'.$vehicleId);
        $reportData = ArrayUtils::tryGet($result, 'data');

        if (empty($reportData)) {
            return $this->redirect()->toRoute('user-home');
        }

        try {
            $apiUrl = ReportUrlBuilder::printReport($reportData['documentId']);
            $result = $this->restClient->getPdf($apiUrl);

            $response = new Response();
            $response->setContent($result);
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');

            return $response;
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
            throw $e;
        } catch (\Exception $e) {
            $this->addErrorMessages($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return ReasonForRefusalDto[]
     */
    private function getReasonsForRefusal()
    {
        return $this->catalogService->getReasonsForRefusal();
    }

    private function assertRefuseToTest($vtsId)
    {
        $this->createRefuseToTestAssertion()->assertGranted($vtsId);
    }

    /**
     * @return RefuseToTestAssertion
     */
    private function createRefuseToTestAssertion()
    {
        $refuseToTestAssertion = new RefuseToTestAssertion($this->authorisationService);

        return $refuseToTestAssertion;
    }

    /**
     * @param $vehicleId
     * @param $vehicleSource
     *
     * @return \DvsaCommon\Dto\Vehicle\VehicleDto
     */
    private function getVehicle($vehicleId, $vehicleSource)
    {
        if ($vehicleSource === VehicleSearchSource::DVLA) {
            return $this->vehicleService->getDvlaVehicleById($vehicleId);
        } else {
            return $this->vehicleService->getDvsaVehicleById($vehicleId);
        }
    }

    private function createBackToConfirmationLink($id, $noRegistration, $source)
    {
        return $this->url()->fromRoute(
            (
                StartTestConfirmationController::ROUTE_START_TEST_CONFIRMATION
            ),
            [
                'id' => $id,
                StartTestConfirmationController::ROUTE_PARAM_NO_REG => $noRegistration,
                StartTestConfirmationController::ROUTE_PARAM_SOURCE => $source,
            ],
            [
            ]
        );
    }

    private function createFormAction($source, $noReg)
    {
        return $this->url()->fromRoute(
            self::ROUTE_REFUSE_TO_TEST_REASON,
            [],
            [
                'query' => [
                    StartTestConfirmationController::ROUTE_PARAM_NO_REG => $noReg,
                    StartTestConfirmationController::ROUTE_PARAM_SOURCE => $source,
                ],
            ],
            true
        );
    }

    private function createViewModel($source, $noReg, $vehicleId)
    {
        $obfuscatedVehicleId = $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);

        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        return new ViewModel(
            [
                'reasonForRefusal' => $this->getReasonsForRefusal(),
                'backToSearchLink' => $this->createBackToConfirmationLink(
                    $obfuscatedVehicleId, $noReg, $source
                ),
                'isDisplayRegistration' => $noReg ? false : true,
                'vehicle' => $this->getVehicle((int) $vehicleId, $source),
                'formAction' => $this->createFormAction($source, $noReg),
            ]
        );
    }
}
