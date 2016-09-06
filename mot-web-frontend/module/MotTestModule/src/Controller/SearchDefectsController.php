<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use DateTime;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class SearchDefectsController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__SEARCH = 'Search for a defect';

    /*
     * Due to constraints in the API, we are not using the 'start' or 'end'
     * query parameters. Instead we just get all the search results at once.
     */
    const WE_ARE_NOT_USING_THIS_PARAMETER = 0;

    const QUERY_PARAM_SEARCH_TERM = 'q';
    const QUERY_PARAM_SEARCH_PAGE = 'p';

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
        $page = $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_PAGE);
        if (empty($page)) {
            $page = 1;
        }

        $vehicleClassCode = 0;

        /** @var MotTestDto $motTest */
        $motTest = null;
        $isReinspection = false;
        $isDemoTest = false;
        $paginator = null;
        $defects = null;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemoTest = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
            $vehicleClassCode = $motTest->getVehicleClass()->getCode();
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d',
            $motTest->getVehicle()->getFirstUsedDate())->format('j M Y');
        $vehicleMakeAndModel = ucwords(strtolower($motTest->getVehicle()->getMakeAndModel()));

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->enableGdsLayout('Search for a defect', '');

        if ($searchTerm !== '' && !is_null($searchTerm)) {
            $defects = $this->getSearchResultsFromApi();
        }

        if (!is_null($defects)) {
            $defects = $this->addInspectionManualReferenceUrls($defects, $vehicleClassCode);
            $paginator = new Paginator(new ArrayAdapter($defects->getDefects()));
            $paginator->setItemCountPerPage(10);
            $paginator->setPageRange(5);
            $paginator->setCurrentPageNumber($page);
        }

        $hasResults = !empty($defects);

        return $this->createViewModel('defects/search.twig', [
            'motTestNumber' => $motTestNumber,
            'identifiedDefects' => $identifiedDefects,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'searchTerm' => $searchTerm,
            'hasResults' => $hasResults,
            'page' => $page,
            'paginator' => $paginator,
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
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection)
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
        } else {
            // Normal test
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl,
            ];
        }
        $breadcrumbs += [self::CONTENT_HEADER_TYPE__SEARCH => ''];

        return $breadcrumbs;
    }

    /**
     * Due to time constraints I wasn't able to change the API to make it work
     * in a sane way. So we just fetch all the results for the search term.
     *
     * This doesn't work too badly. A search term returning >500 results only
     * takes around half a second to return.
     *
     * This will return a DefectCollection containing all the defects which
     * correspond to the search term, which can then be used in a Paginator.
     *
     * The API is broken in two ways:
     *  the 'end' parameter doesn't do anything;
     *  the 'count' which the API returns is always 10 or less, regardless of how
     *      many results there actually are.
     *
     * @return DefectCollection|null
     *
     * @see Paginator
     */
    private function getSearchResultsFromApi()
    {
        $searchTerm = $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_TERM);
        if ($searchTerm === '') {
            return null;
        }

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $searchResults = null;

        try {
            $params =
                [
                    'search' => $searchTerm,
                    'start' => self::WE_ARE_NOT_USING_THIS_PARAMETER,
                    'end' => self::WE_ARE_NOT_USING_THIS_PARAMETER,
                ];

            $endPoint = MotTestUrlBuilder::motSearchTestItem($motTestNumber);

            /**
             * @var array
             */
            $resultsFromApi = $this
                ->getRestClient()
                ->getWithParamsReturnDto($endPoint, $params);

            $searchResults = DefectCollection::fromSearchResults($resultsFromApi);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return $searchResults;
    }

    /**
     * @param DefectCollection $defects
     * @param string           $vehicleClassCode
     *
     * @return DefectCollection
     */
    private function addInspectionManualReferenceUrls(DefectCollection $defects, $vehicleClassCode)
    {
        foreach ($defects as $defect) {
            /* @var Defect $defect */
            // Generate inspection manual reference URL for each defect
            $inspectionManualReference = trim($defect->getInspectionManualReference());
            $vehicleClass = (intval($vehicleClassCode) > 2) ? 4 : 1;

            if (strlen($inspectionManualReference)) {
                $inspectionManualReferenceParts = explode('.', $inspectionManualReference);
                if (count($inspectionManualReferenceParts) >= 2) {
                    $defect->setInspectionManualReferenceUrl(sprintf('documents/manuals/m%ds0%s000%s01.htm',
                        $vehicleClass, $inspectionManualReferenceParts[0], $inspectionManualReferenceParts[1]));
                }
            }
        }

        return $defects;
    }
}
