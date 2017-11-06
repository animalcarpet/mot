<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleClass;
use Dvsa\Mot\Frontend\MotTestModule\Service\ReasonForRejectionPaginator;
use Dvsa\Mot\Frontend\MotTestModule\Service\SearchReasonForRejectionService;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class SearchDefectsController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS = 'Non-MOT test';
    const CONTENT_HEADER_TYPE__SEARCH = 'Search for a defect';

    /*
     * Due to constraints in the API, we are not using the 'start' or 'end'
     * query parameters. Instead we just get all the search results at once.
     */
    const WE_ARE_NOT_USING_THIS_PARAMETER = 0;

    const QUERY_PARAM_SEARCH_TERM = 'q';
    const QUERY_PARAM_SEARCH_PAGE = 'p';

    private $reasonForRejectionService;

    public function __construct(SearchReasonForRejectionService $reasonForRejectionService)
    {
        $this->reasonForRejectionService = $reasonForRejectionService;
    }

    /**
     * Handles the root categories view when the search functionality is enabled. No category is selected.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/search
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $searchTerm = $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_TERM);
        $page = (int) $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_PAGE, 0);
        if (empty($page)) {
            $page = 1;
        }

        $vehicleClassCode = 0;

        /** @var MotTest $motTest */
        $motTest = null;
        /** @var DvsaVehicle $vehicle */
        $vehicle = null;
        $isReinspection = false;
        $isDemoTest = false;
        $isNonMotTest = false;
        $paginator = null;
        $defects = null;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());
            $testType = $motTest->getTestTypeCode();
            $isDemoTest = MotTestType::isDemo($testType);
            $isReinspection = MotTestType::isReinspection($testType);
            /** @var VehicleClass $vehicleClassCode */
            $vehicleClassCode = $vehicle->getVehicleClass();
            $isNonMotTest = MotTestType::isNonMotTypes($testType);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d',
            $vehicle->getFirstUsedDate())->format('j M Y');
        $dvsaVehicleViewModel = new DvsaVehicleViewModel($vehicle);
        $vehicleMakeAndModel = $dvsaVehicleViewModel->getMakeAndModel();

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $isNonMotTest);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->enableGdsLayout('Search for a defect', '');
        $this->setHeadTitle('Search for a defect');

        if ($searchTerm !== '' && !is_null($searchTerm)) {
            $paginator = $this->getSearchResultsFromApi($searchTerm, $vehicle->getVehicleClass()->getCode(), $page);
        }

        $hasResults = !is_null($paginator);
        $isRetest = $motTest->getTestTypeCode() === MotTestTypeCode::RE_TEST;

        return $this->createViewModel('defects/search.twig', [
            'motTestNumber' => $motTestNumber,
            'identifiedDefects' => $identifiedDefects,
            'vehicle' => $vehicle,
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'searchTerm' => $searchTerm,
            'hasResults' => $hasResults,
            'page' => $page,
            'paginator' => $paginator,
            'isRetest' => $isRetest,
        ]);
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
     * @param bool $isDemo
     * @param bool $isReinspection
     * @param bool $isNonMotTest
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $motTestNumber]);

        $breadcrumbs = [];
        if ($isDemo) {
            // Demo test
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl,
            ];
        } elseif ($isReinspection) {
            // Reinspection
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl,
            ];
        } elseif ($isNonMotTest) {
            // Non-MOT
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS => $motTestResultsUrl,
            ];
        } else {
            // Normal test
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl,
            ];
        }
        $breadcrumbs += [self::CONTENT_HEADER_TYPE__SEARCH => ''];

        return $breadcrumbs;
    }

    private function getSearchResultsFromApi(string $searchTerm, string $vehicleClassCode, int $page): ReasonForRejectionPaginator
    {
        return $this->reasonForRejectionService->search($searchTerm, $vehicleClassCode, $page);
    }
}
