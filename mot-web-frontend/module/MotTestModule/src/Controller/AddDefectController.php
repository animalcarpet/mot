<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\MotTestModule\Exception\DefectTypeNotFoundException;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;

class AddDefectController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';

    const DEFECT_TYPE_ADVISORY = 'advisory';
    const DEFECT_TYPE_PRS = 'prs';
    const DEFECT_TYPE_FAILURE = 'failure';

    /**
     * Handles the screen for adding a defect.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/defect?l1=0&l2=0&l3=undefined&l4=undefined&rfrIndex=0&type=Advisory
     *
     * @return ViewModel
     */
    public function addAction()
    {
        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::RFR_LIST)) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $categoryId = $this->params()->fromRoute('categoryId');
        $defectId = $this->params()->fromRoute('defectId');
        $type = $this->params()->fromRoute('type');

        $isReinspection = null;
        $isDemoTest = null;
        /** @var MotTestDto $motTest */
        $motTest = null;
        $defects = null;
        $title = '';
        $errorMessages = '';

        try {
            $title = $this->createTitle($type);
            $defect = $this->getDefect($motTestNumber, $defectId);

            // If the defect is added as an advisory, then display the defect's advisory text on the Add Defect page.
            // Else, if the defect isn't added as an advisory, then display the defect's description on the Add Defect page.
            $type === self::DEFECT_TYPE_ADVISORY ?
                $defectDetail = $defect->getAdvisoryText() : $defectDetail = $defect->getDescription();

            $this->enableGdsLayout($title, '');
            $this->layout()->setVariable('pageTertiaryTitle', $defectDetail);

            $type = $this->transformDefectTypeForView($type);

            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemoTest = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
            $request = $this->getRequest();

            if ($request->isPost()) {

                $apiPath = MotTestUrlBuilder::motTestRfr($motTest->getMotTestNumber());

                $locationLateral = $request->getPost('locationLateral');
                $locationLongitudinal = $request->getPost('locationLongitudinal');
                $locationVertical = $request->getPost('locationVertical');
                $comment = $request->getPost('comment');
                $failureDangerous = $request->getPost('failureDangerous') ? true : false;

                // Data to be sent to the API to add a defect.
                $data = [
                    'rfrId' => $defectId,
                    'type' => $this->transformDefectTypeForApiPost($type),
                    'locationLateral' => ($locationLateral !== 'n/a') ? $locationLateral : null,
                    'locationLongitudinal' => ($locationLongitudinal !== 'n/a') ? $locationLongitudinal : null,
                    'locationVertical' => ($locationVertical !== 'n/a') ? $locationVertical : null,
                    'comment' => $comment,
                    'failureDangerous' => $failureDangerous,
                ];

                $motTestDefectId = $this->getRestClient()->post($apiPath, $data);
                if (!empty($motTestDefectId)) {
                    $this->addSuccessMessage(sprintf(
                        '<strong>This %s has been added:</strong><br> %s',
                        $type,
                        $defectDetail
                    ));
                    return $this->redirect()->toUrl($this->getCategoryUrl($motTestNumber, $categoryId));
                }
            }

        } catch (DefectTypeNotFoundException $e){
            return $this->notFoundAction();
        } catch (RestApplicationException $e) {
            $errorMessages = $e->getErrors()[0];
        }

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $categoryId, $title);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $backUrl = $this->getCategoryUrl($motTestNumber, $categoryId);

        return $this->createViewModel('defects/add-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => $defectId,
            'type' => $type,
            'breadcrumbs' => $breadcrumbs,
            'backUrl' => $backUrl,
            'errorMessages' => $errorMessages,
        ]);
    }

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param bool $isDemo
     * @param bool $isReinspection
     *
     * @param int $categoryId
     * @param string $title
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $categoryId, $title)
    {
        $breadcrumbs = [];

        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $motTestNumber]);
        $motAddDefectUrl = $this->getCategoryUrl($motTestNumber, $categoryId);

        if ($isDemo) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl];
        } elseif ($isReinspection) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl];
        } else {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl];
        }
        $breadcrumbs += [ 'Add a defect' => $motAddDefectUrl];
        $breadcrumbs += [ $title => ''];

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
     * @param string $defectType
     *
     * @throws DefectTypeNotFoundException
     *
     * @return string
     */
    private function createTitle($defectType)
    {
        switch (strtolower($defectType)) {
            case self::DEFECT_TYPE_ADVISORY:
                $title = 'Add an advisory';
                break;
            case self::DEFECT_TYPE_PRS:
                $title = 'Add a PRS';
                break;
            case self::DEFECT_TYPE_FAILURE:
                $title = 'Add a failure';
                break;
            default:
                throw new DefectTypeNotFoundException();
        }

        return $title;
    }

    /**
     * @param $type
     *
     * @return string
     *
     * @throws DefectTypeNotFoundException
     */
    private function transformDefectTypeForView($type)
    {
        if (strtolower($type) === 'prs') {
            return 'PRS';
        }

        return $type;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function transformDefectTypeForApiPost($type)
    {
        // Defect type 'failure' is displayed in the view, which needs transformed to 'FAIL' for posting to the API.
        if('failure' == strtolower($type)){
            return 'FAIL';
        }

        return strtoupper($type);
    }

    /**
     * @param int $motTestNumber
     * @param int $categoryId
     *
     * @return string
     */
    private function getCategoryUrl($motTestNumber, $categoryId)
    {
        return $this->url()->fromRoute(
            'mot-test-defects/categories/category',
            ['motTestNumber' => $motTestNumber, 'categoryId' => $categoryId]
        );
    }

    /**
     * @param int $motTestNumber
     * @param int $defectId
     *
     * @return Defect
     */
    private function getDefect($motTestNumber, $defectId)
    {
        $getDefectApiUrl = MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString();

        return Defect::fromApi($this->getRestClient()->get($getDefectApiUrl)['data']);
    }
}
