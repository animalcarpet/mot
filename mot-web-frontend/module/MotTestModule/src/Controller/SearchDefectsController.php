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
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\View\Model\ViewModel;

class SearchDefectsController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__SEARCH = 'Search for a defect';

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
        $vehicleClassCode = 0;

        /** @var MotTestDto $motTest */
        $motTest = null;
        $isReinspection = false;
        $isDemoTest = false;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemoTest = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
            $vehicleClassCode = $motTest->getVehicleClass()->getCode();
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $observedDefects = ObservedDefectCollection::fromMotApiData($motTest);
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d',
            $motTest->getVehicle()->getFirstUsedDate())->format('j M Y');
        $vehicleMakeAndModel = ucwords(strtolower($motTest->getVehicle()->getMakeAndModel()));

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->enableGdsLayout('Search for a defect', '');

        $defects = $this->getSearchResultsFromApi();

        if (!is_null($defects)) {
            $defects = $this->addInspectionManualReferenceUrls($this->getSearchResultsFromApi(), $vehicleClassCode);
        }

        $defectsCount = is_null($defects) ? 0 : count($defects->getDefects());
        $hasResults = !empty($defects);

        // TODO: Remove these hardcoded values as part of BL-3075
        $page = 0;

        return $this->createViewModel('defects/search.twig', [
            'motTestNumber' => $motTestNumber,
            'observedDefects' => $observedDefects,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'searchTerm' => $searchTerm,
            'hasResults' => $hasResults,
            'numberOfResults' => $defectsCount,
            'defects' => $defects,
            'page' => $page,
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
     * @return DefectCollection|null
     */
    private function getSearchResultsFromApi()
    {
        $searchTerm = $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_TERM);
        if ($searchTerm === '') {
            return null;
        }

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $searchResults = null;

        // TODO: Remove these hardcoded values as part of BL-3075
        $start = 0;
        $end = 9999;

        try {
            $params =
                [
                    'search' => $searchTerm,
                    'start' => $start,
                    'end' => $end,
                ];

            $endPoint = MotTestUrlBuilder::motSearchTestItem($motTestNumber);

            /**
             * @var array $resultsFromApi
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
