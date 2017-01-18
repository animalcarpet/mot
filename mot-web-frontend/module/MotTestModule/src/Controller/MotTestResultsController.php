<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaMotTest\Controller\DvsaVehicleViewModel;
use DvsaMotTest\Model\OdometerReadingViewObject;
use Vehicle\ViewModel\VehicleViewModel;
use Zend\Http\Response;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;

/**
 * MotTestResultsController handles MOT Test results views.
 */
class MotTestResultsController extends AbstractDvsaMotTestController
{
    const DATE_FORMAT = 'j F Y';
    const DATETIME_FORMAT = 'd M Y H:i';
    const ODOMETER_VALUE_REQUIRED_MESSAGE = 'Odometer value must be entered to update odometer reading';
    const ODOMETER_FORM_ERROR_MESSAGE = 'The odometer reading should be a valid number between 0 and 999999';
    const TEST_DOCUMENT_VT30 = 'VT30';
    const TEST_DOCUMENT_VT32 = 'VT32';
    const ROUTE_MOT_TEST = 'mot-test';
    const ROUTE_MOT_TEST_SHORT_SUMMARY = 'mot-test/short-summary';

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var OdometerReadingViewObject
     */
    private $odometerViewObject;

    /**
     * @var MotTestService $motTestServiceClient
     */
    public $motTestServiceClient;

    /**
     * @var VehicleService $vehicleServiceClient
     */
    public $vehicleServiceClient;

    protected function getMotTestServiceClient()
    {
        if (!$this->motTestServiceClient) {
            $sm = $this->getServiceLocator();
            $this->motTestServiceClient = $sm->get(MotTestService::class);
        }

        return $this->motTestServiceClient;
    }

    protected function getVehicleServiceClient()
    {
        if (!$this->vehicleServiceClient) {
            $sm = $this->getServiceLocator();
            $this->vehicleServiceClient = $sm->get(VehicleService::class);
        }

        return $this->vehicleServiceClient;
    }

    /**
     * MotTestResultsController constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param OdometerReadingViewObject $odometerViewObject
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        OdometerReadingViewObject $odometerViewObject
    )
    {
        $this->authorisationService = $authorisationService;
        $this->odometerViewObject = $odometerViewObject;
    }

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $motTestNumber = (int)$this->params('motTestNumber', 0);

        /** @var MotTest $motTest */
        $motTest = null;
        $isDemo = false;
        $isReinspection = false;
        $isNonMotTest = false;
        /** @var DvsaVehicle $vehicle */
        $vehicle = null;
        /** @var DvsaVehicleViewModel $vehicleViewModel*/
        $vehicleViewModel = null;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());
            $vehicleViewModel = new DvsaVehicleViewModel($vehicle);
            $this->getPerformMotTestAssertion()->assertGranted($motTest);
            $testType = $motTest->getTestTypeCode();
            $isDemo = MotTestType::isDemo($testType);
            $isNonMotTest = MotTestType::isNonMotTypes($testType);
            $isReinspection = MotTestType::isReinspection($testType);
            $isTester = $this->getAuthorizationService()->isTester();
            $currentVts = $this->getIdentity()->getCurrentVts();

            if (!$isDemo && $isTester && !$currentVts) {
                return $this->redirectToSelectLocation($motTestNumber);
            };

            $apiUrl = MotTestUrlBuilder::odometerReadingNotices($motTestNumber)->toString();
            $readingNotices = $this->getRestClient()->get($apiUrl);
        } catch (ValidationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        if ($motTest instanceof MotTest) {
            $this->odometerViewObject->setValue($motTest->getOdometerValue());
            $this->odometerViewObject->setUnit($motTest->getOdometerUnit());
            $this->odometerViewObject->setResultType($motTest->getOdometerResultType());
        }

        if (!empty($readingNotices)) {
            $this->odometerViewObject->setNotices($readingNotices['data']);
        }

        if ($isDemo) {
            $breadcrumb = 'Training test';
        } elseif ($isReinspection) {
            $breadcrumb = 'MOT test reinspection';
        } elseif ($isNonMotTest) {
            $breadcrumb = 'Non-MOT test';
        } else {
            $breadcrumb = 'MOT test results';
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => [$breadcrumb => '']]);
        if($isNonMotTest){
            $this->layout()->setVariable('pageTitle', 'Non-MOT test');
        } else {
            $this->layout()->setVariable('pageTitle', 'MOT test results');
        }

        //TODO Change to formatting if its not correct
//        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d',
//            $vehicleViewModel->getFirstUsedDate());

        /** @var MotTest $originalMotTest */
        $originalMotTest = null;
        if(!empty($motTest->getMotTestOriginalNumber())){
            $originalMotTest = $this->getMotTestFromApi($motTest->getMotTestOriginalNumber());
        }

        $motTestResults = new MotTestResults($motTest, $originalMotTest);
        $vehicleMakeAndModel = $vehicleViewModel->getMakeAndModel();

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);
        $isRetest = $motTest->getTestTypeCode() === MotTestTypeCode::RE_TEST;

        $this->addTestNumberAndTypeToGtmDataLayer($motTest->getMotTestNumber(), $motTest->getTestTypeCode());

        return $this->createViewModel('mot-test/test-results-entry.twig', [
            'isDemo' => $isDemo,
            'motTest' => $motTest,
            'motTestResults' => $motTestResults,
            'odometerReading' => $this->odometerViewObject,
            'vehicle' => $vehicle,
            'vehicleViewModel' => $vehicleViewModel,
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => DateTimeDisplayFormat::textDateShort($vehicleViewModel->getFirstUsedDate()),
            'shouldDisableSubmitButton' => $motTestResults->shouldDisableSubmitButton(),
            'identifiedDefects' => $identifiedDefects,
            'isRetest' => $isRetest,
            'isNonMotTest' => $isNonMotTest,
        ]);
    }

    /**
     * @param string $testType
     *
     * @return string
     */
    public static function getTestName($testType)
    {
        if ($testType === MotTestTypeCode::RE_TEST) {
            $testName = 'MOT re-test';
        } elseif ($testType === MotTestTypeCode::NON_MOT_TEST) {
            $testName = 'Non-MOT test';
        } elseif (MotTestType::isReinspection($testType)) {
            $testName = 'MOT reinspection';
        } else {
            $testName = 'MOT test';
        }

        return $testName;
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
     * @return WebPerformMotTestAssertion
     */
    private function getPerformMotTestAssertion()
    {
        return $this->getServiceLocator()->get(WebPerformMotTestAssertion::class);
    }

    /**
     * @param $motTestNumber
     *
     * @return Response redirection
     */
    private function redirectToSelectLocation($motTestNumber)
    {
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        /** @var RouteMatch $routeMatch */
        $route = $routeMatch->getMatchedRouteName();
        $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
        $container->persistConfig(['route' => $route, 'params' => ['motTestNumber' => $motTestNumber]]);

        return $this->redirect()->toRoute('location-select');
    }

    /**
     * @param string $motTestNumber
     * @param int $testTypeId
     */
    private function addTestNumberAndTypeToGtmDataLayer($motTestNumber, $testTypeId)
    {
        $this->gtmDataLayer([
            'testId' => $motTestNumber,
            'testType' => $testTypeId,
        ]);
    }
}
