<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Core\Controller\AbstractAuthActionController;
use DateTime;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\Model\OdometerReadingViewObject;
use Zend\Http\Response;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;

/**
 * MotTestResultsController handles MOT Test results views.
 */
class MotTestResultsController extends AbstractAuthActionController
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
     * MotTestResultsController constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $motTestNumber = (int) $this->params('motTestNumber', 0);

        /** @var MotTestDto $motTest */
        $motTest = null;
        $odometerReading = new OdometerReadingViewObject();
        $isDemo = false;
        $isReinspection = false;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $this->getPerformMotTestAssertion()->assertGranted($motTest);
            $testType = $motTest->getTestType();
            $isDemo = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
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

        if ($motTest instanceof MotTestDto && $motTest->getOdometerReading() !== null) {
            $odometerReading->setOdometerReadingValuesMap($motTest->getOdometerReading());
        }

        if (!empty($readingNotices)) {
            $odometerReading->setNotices($readingNotices['data']);
        }

        if ($isDemo) {
            $breadcrumb = 'Training test';
        } elseif ($isReinspection) {
            $breadcrumb = 'MOT test reinspection';
        } else {
            $breadcrumb = 'MOT test results';
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => [$breadcrumb => '']]);
        $this->layout()->setVariable('pageTitle', 'MOT test results');

        $submissionStatus = isset($motTest->getPendingDetails()['currentSubmissionStatus'])
            ? $motTest->getPendingDetails()['currentSubmissionStatus'] : null;
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d',
            $motTest->getVehicle()->getFirstUsedDate())->format('j M Y');

        $motTestResults = new MotTestResults($motTest);
        $vehicleMakeAndModel = ucwords(strtolower($motTest->getVehicle()->getMakeAndModel()));

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);
        $isRetest = $motTest->getTestType()->getCode() === MotTestTypeCode::RE_TEST;

        $this->addTestNumberAndTypeToGtmDataLayer($motTest->getMotTestNumber(), $motTest->getTestType()->getId());

        return $this->createViewModel('mot-test/test-results-entry.twig', [
            'isDemo' => $isDemo,
            'motTest' => $motTest,
            'motTestResults' => $motTestResults,
            'odometerReading' => $odometerReading,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'shouldDisableSubmitButton' => $submissionStatus == 'INCOMPLETE',
            'identifiedDefects' => $identifiedDefects,
            'isRetest' => $isRetest,
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
     * @param array  $variables
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
     * @param $motTestNumber
     *
     * @return mixed
     */
    private function getMotTestFromApi($motTestNumber)
    {
        $apiUrl = MotTestUrlBuilder::motTest($motTestNumber)->toString();
        $result = $this->getRestClient()->get($apiUrl);

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
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
            'testId'   => $motTestNumber,
            'testType' => $testTypeId,
        ]);
    }
}
