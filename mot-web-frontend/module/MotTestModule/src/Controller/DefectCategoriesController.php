<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use DateTime;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategoryCollection;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Handles the "Defects categories" view.
 */
class DefectCategoriesController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var DefectsContentBreadcrumbsBuilder
     */
    private $breadcrumbsBuilder;

    /**
     * DefectCategoriesController constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param DefectsContentBreadcrumbsBuilder $breadcrumbsBuilder
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService,
                                DefectsContentBreadcrumbsBuilder $breadcrumbsBuilder)
    {
        $this->authorisationService = $authorisationService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
    }

    /**
     * Handles the root categories view. No category is selected.
     *
     * See https://mot-rfr.herokuapp.com/rfr/browser.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->categoryAction();
    }

    /**
     * This action handles requests to mot-test/:motTestNumber/defects. That URL does not provide any specific
     * functionality and its only purpose is to provide an hierarchical structure of URLs. A controller handler was
     * added to avoid having dead links.
     *
     * @return Response
     */
    public function redirectToCategoriesIndexAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');

        return $this->redirect()->toRoute('mot-test-defects/categories', ['motTestNumber' => $motTestNumber]);
    }

    /**
     * Handles the categories browsing view when a category or sub-category was selected.
     *
     * See https://mot-rfr.herokuapp.com/rfr/browser?l1=0
     *
     * @return ViewModel
     */
    public function categoryAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $categoryId = (int) $this->params()->fromRoute('categoryId');

        /** @var MotTestDto $motTest */
        $motTest = null;
        $defectCategories = null;

        $isDemo = false;
        $isReinspection = false;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemo = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
            $defectCategories = $this->getDefectCategories($motTestNumber, $categoryId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        if (true === $this->isDefectsParent($defectCategories)) {
            return $this->defectsForCategoryAction($motTestNumber, $defectCategories,
                $motTest, $isDemo, $isReinspection, $categoryId);
        }

        $this->enableGdsLayout('Defect categories', '');

        $vehicleFirstUsedDate = $motTest->getVehicle()->getFirstUsedDate();
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d', $vehicleFirstUsedDate)->format('j M Y');

        $breadcrumbs = $this->getBreadcrumbs($isDemo, $isReinspection);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $observedDefects = ObservedDefectCollection::fromMotApiData($motTest);

        return $this->createViewModel('defects/categories.twig', [
            'motTest' => $motTest,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => ucwords(strtolower($motTest->getVehicle()->getMakeAndModel())),
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'observedDefects' => $observedDefects,
            'defectCategories' => $defectCategories,
            'browseColumns' => $defectCategories->getColumnCountForHtml(),
        ]);
    }

    /**
     * Handles the view for the last category which has a list of defects associated.
     *
     * See https://mot-rfr.herokuapp.com/rfr/lister?l1=0&l2=0
     *
     *
     *
     * @param int                         $motTestNumber
     * @param ComponentCategoryCollection $category
     * @param MotTestDto                  $motTest
     * @param bool                        $isDemo
     * @param bool                        $isReinspection
     * @param bool                        $categoryId
     *
     * @return ViewModel
     */
    public function defectsForCategoryAction(
        $motTestNumber,
        ComponentCategoryCollection $category,
        MotTestDto $motTest,
        $isDemo,
        $isReinspection,
        $categoryId
    ) {
        $this->enableGdsLayout('Defects', '');

        $vehicleClassCode = $motTest->getVehicleClass()->getCode();

        $defects = $this->addInspectionManualReferenceUrls($category->getComponentCategory()->getDefectsCollection(), $vehicleClassCode);
        
        $observedDefects = ObservedDefectCollection::fromMotApiData($motTest);

        $breadcrumbs = $this->getBreadcrumbs($isDemo, $isReinspection);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $contentBreadcrumbs = $this->breadcrumbsBuilder->getContentBreadcrumbs($category, $motTestNumber);

        $vehicleFirstUsedDate = $motTest->getVehicle()->getFirstUsedDate();
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d', $vehicleFirstUsedDate)->format('j M Y');

        return $this->createViewModel('defects/defects-for-category.twig', [
            'motTest' => $motTest,
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => ucwords(strtolower($motTest->getVehicle()->getMakeAndModel())),
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'defects' => $defects->getDefects(),
            'contentBreadcrumbs' => $contentBreadcrumbs,
            'observedDefects' => $observedDefects,
        ]);
    }

    /**
     * Check if the category has any subcategories, i.e., is it the parent of
     * some reasons for rejection or the parent of another category.
     *
     * @param ComponentCategoryCollection $defectCategories
     *
     * @return bool
     */
    private function isDefectsParent(ComponentCategoryCollection $defectCategories)
    {
        return count($defectCategories->getComponentCategory()->getDefectsCollection()->getDefects()) !== 0;
    }

    /**
     * Get data from API endpoint.
     *
     * @param $path
     * @param null $params
     *
     * @return mixed
     */
    protected function getDataFromApi($path, $params = null)
    {
        $result = $this->getRestClient()->getWithParamsReturnDto($path, $params);

        return $result['data'];
    }

    /**
     * @param $motTestNumber
     * @param $categoryId
     *
     * @return ComponentCategoryCollection
     */
    private function getDefectCategories($motTestNumber, $categoryId)
    {
        $isVe = $this->authorisationService->hasRole(Role::VEHICLE_EXAMINER);

        $dataFromApi = [];
        $index = 0;
        $dataFromApi[$index] = $this->getDataFromApi(
            MotTestUrlBuilder::motTestItem(
                $motTestNumber,
                $categoryId
            )
        );

        // Traverse the tree of component categories.
        while ($this->isNotRfrHome($dataFromApi[$index])) {
            $index += 1;
            $dataFromApi[$index] = $this->getDataFromApi(
                MotTestUrlBuilder::motTestItem(
                    $motTestNumber,
                    $dataFromApi[$index - 1]['testItemSelector']['parentTestItemSelectorId']
                )
            );
        }

        // The parent of the leftmost-but-one column is always 0, or 'RFR Home'
        $dataFromApi[$index] = $this->getDataFromApi(
            MotTestUrlBuilder::motTestItem(
                $motTestNumber,
                0
            )
        );
        // Here we reverse the tree. We want the the columns stored in order of left->right.
        $dataFromApi = array_reverse($dataFromApi);

        $componentCategoryCollection = ComponentCategoryCollection::fromDataFromApi($dataFromApi, $isVe);

        return $componentCategoryCollection;
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

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param bool $isDemo
     * @param bool $isReinspection
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection)
    {
        $breadcrumbs = [];

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $motTestNumber]);

        if ($isDemo) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl];
        } elseif ($isReinspection) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl];
        } else {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl];
        }

        $breadcrumbs += ['Add a defect' => ''];

        return $breadcrumbs;
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
     * @param array $componentCategory
     *
     * @return bool
     */
    private function isNotRfrHome(array $componentCategory)
    {
        return $componentCategory['testItemSelector']['id'] !== 0;
    }
}