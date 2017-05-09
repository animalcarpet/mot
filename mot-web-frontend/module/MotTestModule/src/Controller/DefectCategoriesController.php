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
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategoryCollection;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
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
    const CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS = 'Non-MOT test';

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

        /** @var MotTest $motTest */
        $motTest = null;
        /** @var DvsaVehicle $vehicle */
        $vehicle = null;

        $defectCategories = null;

        $isDemo = false;
        $isReinspection = false;
        $isRetest = false;
        $isNonMotTest = false;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $vehicle = $this->getVehicleServiceClient()->getDvsaVehicleByIdAndVersion($motTest->getVehicleId(), $motTest->getVehicleVersion());
            $testType = $motTest->getTestTypeCode();
            $isDemo = MotTestType::isDemo($testType);
            $isReinspection = MotTestType::isReinspection($testType);
            $isRetest = MotTestType::isRetest($testType);
            $defectCategories = $this->getDefectCategories($motTestNumber, $categoryId);
            $isNonMotTest = MotTestType::isNonMotTypes($testType);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        if (true === $this->isDefectsParent($defectCategories)) {
            return $this->defectsForCategoryAction($motTestNumber, $defectCategories,
                $motTest, $vehicle, $isDemo, $isReinspection, $categoryId, $isRetest, $isNonMotTest);
        }

        $this->enableGdsLayout('Defect categories', '');
        $this->setHeadTitle('Defect categories');

        $vehicleFirstUsedDate = $vehicle->getFirstUsedDate();
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d', $vehicleFirstUsedDate)->format('j M Y');

        $breadcrumbs = $this->getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);

        return $this->createViewModel('defects/categories.twig', [
            'motTest' => $motTest,
            'vehicle' => $vehicle,
            'vehicleMakeAndModel' => $vehicle->getMakeAndModel(),
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'identifiedDefects' => $identifiedDefects,
            'defectCategories' => $defectCategories,
            'browseColumns' => $defectCategories->getColumnCountForHtml(),
            'isRetest' => $isRetest,
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
     * @param MotTest                     $motTest
     * @param DvsaVehicle                 $vehicle
     * @param bool                        $isDemo
     * @param bool                        $isReinspection
     * @param bool                        $categoryId
     * @param bool                        $isRetest
     * @param bool                        $isNonMotTest
     *
     * @return ViewModel
     */
    public function defectsForCategoryAction(
        $motTestNumber,
        ComponentCategoryCollection $category,
        MotTest $motTest,
        DvsaVehicle $vehicle,
        $isDemo,
        $isReinspection,
        $categoryId,
        $isRetest,
        $isNonMotTest
    ) {
        $this->enableGdsLayout('Defects', '');
        $this->setHeadTitle('Defects');

        $vehicleClassCode = $vehicle->getVehicleClass()->getCode();

        $defects = $this->addInspectionManualReferenceUrls($category->getComponentCategory()->getDefectsCollection(), $vehicleClassCode);

        $identifiedDefects = IdentifiedDefectCollection::fromMotApiData($motTest);

        $breadcrumbs = $this->getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $contentBreadcrumbs = $this->breadcrumbsBuilder->getContentBreadcrumbs($category, $motTestNumber);

        $vehicleFirstUsedDate = $vehicle->getFirstUsedDate();
        $vehicleFirstUsedDate = DateTime::createFromFormat('Y-m-d', $vehicleFirstUsedDate)->format('j M Y');

        $dvsaVehicleViewModel = new DvsaVehicleViewModel($vehicle);
        $vehicleMakeAndModel = $dvsaVehicleViewModel->getMakeAndModel();

        return $this->createViewModel('defects/defects-for-category.twig', [
            'motTest' => $motTest,
            'categoryId' => $categoryId,
            'categoryName' => $category->getComponentCategory()->getName(),
            'vehicle' => $vehicle,
            'vehicleMakeAndModel' => $vehicleMakeAndModel,
            'vehicleFirstUsedDate' => $vehicleFirstUsedDate,
            'defects' => $defects->getDefects(),
            'contentBreadcrumbs' => $contentBreadcrumbs,
            'isRetest' => $isRetest,
            'identifiedDefects' => $identifiedDefects,
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

        $dataFromApi = $this->getDataFromApi(
            MotTestUrlBuilder::motTestItem(
                $motTestNumber,
                $categoryId
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
     * @param bool $isNonMotTest
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest)
    {
        $breadcrumbs = [];

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $motTestNumber]);

        if ($isDemo) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl];
        } elseif ($isReinspection) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl];
        } elseif ($isNonMotTest) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS => $motTestResultsUrl];
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
}
