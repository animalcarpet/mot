<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Exception\DefectTypeNotFoundException;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\FlashMessageBuilder;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefect;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Controller for adding a manual advisory.
 */
class AddManualAdvisoryController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TITLE = 'Add a manual advisory';
    const CONTENT_HEADER_TERTIARY = 'Only add a manual advisory if you have searched for a defect and can\'t find a match.';
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__SEARCH_RESULTS = 'Search for a defect';
    const CONTENT_HEADER_TYPE__BROWSE_DEFECTS = 'Add a defect';

    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * @var DefectsJourneyContextProvider
     */
    private $defectsJourneyContextProvider;

    /**
     * AddManualAdvisoryController constructor.
     *
     * @param DefectsJourneyUrlGenerator    $defectsJourneyUrlGenerator
     * @param DefectsJourneyContextProvider $defectsJourneyContextProvider
     */
    public function __construct(DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator,
                                DefectsJourneyContextProvider $defectsJourneyContextProvider)
    {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
        $this->defectsJourneyContextProvider = $defectsJourneyContextProvider;
    }

    /**
     * Handles the screen for adding a manual advisory.
     *
     * See https://mot-rfr.herokuapp.com/rfr/defect/?type=Manual&from=manualBroswer
     *
     * @return ViewModel|Response|array
     */
    public function addAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $categoryId = null;
        $defectId = null;
        $type = IdentifiedDefect::ADVISORY;

        $title = self::CONTENT_HEADER_TITLE;
        $this->enableGdsLayout($title, '');
        $this->layout()->setVariable('pageTertiaryTitle', self::CONTENT_HEADER_TERTIARY);

        $isReinspection = null;
        $isDemoTest = null;
        /** @var MotTestDto $motTest */
        $motTest = null;
        $errorMessages = '';

        try {
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
                    'rfrId' => 0,
                    'type' => $this->transformDefectTypeForApiPost($type),
                    'locationLateral' => ($locationLateral !== 'n/a') ? $locationLateral : null,
                    'locationLongitudinal' => ($locationLongitudinal !== 'n/a') ? $locationLongitudinal : null,
                    'locationVertical' => ($locationVertical !== 'n/a') ? $locationVertical : null,
                    'comment' => trim($comment),
                    'failureDangerous' => $failureDangerous,
                ];

                $defectDetail = $data['comment'];

                $motTestDefectId = $this->getRestClient()->post($apiPath, $data);
                if (!empty($motTestDefectId)) {
                    $this->addSuccessMessage(FlashMessageBuilder::manualAdvisoryAddedSuccessfully($defectDetail));

                    return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
                }
            }
        } catch (DefectTypeNotFoundException $e) {
            return $this->notFoundAction();
        } catch (RestApplicationException $e) {
            $errorMessages = $e->getErrors()[0];
            if (isset($errorMessages['message']) && 'You must give a description' === $errorMessages['message']) {
                $errorMessages['message'] = 'Manual advisory description - you must give a description';
            }
        }

        $backUrl = $this->defectsJourneyUrlGenerator->goBack();

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $title);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->createViewModel('defects/add-defect.twig', [
            'backUrl' => $backUrl,
            'breadcrumbs' => $breadcrumbs,
            'categoryId' => $categoryId,
            'context' => $this->defectsJourneyContextProvider->getContextForBackUrlText(),
            'defectId' => $defectId,
            'errorMessages' => $errorMessages,
            'isManualAdvisory' => true,
            'motTestNumber' => $motTestNumber,
            'type' => $type,
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
     * @param bool   $isDemo
     * @param bool   $isReinspection
     * @param string $title
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $title)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $motTestNumber]);

        $context = $this->defectsJourneyContextProvider->getContext();
        $breadcrumbs = [];
        {
            if ($isDemo) {
                // Demo test
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl,
                ];
            } elseif ($isReinspection) {
                // Re-inspection
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl,
                ];
            } else {
                // Normal test
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl,
                ];
            }
        }

        switch ($context) {
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__BROWSE_DEFECTS => $this->defectsJourneyUrlGenerator->goBack(),
                ];
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__BROWSE_DEFECTS => $this->defectsJourneyUrlGenerator->goBack(),
                ];
                break;
            }
            case DefectsJourneyContextProvider::SEARCH_CONTEXT === $context: {
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__SEARCH_RESULTS => $this->defectsJourneyUrlGenerator->goBack(),
                ];
            }
        }
        $breadcrumbs += [$title => ''];

        return $breadcrumbs;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function transformDefectTypeForApiPost($type)
    {
        /*
         * Defect type 'failure' is displayed in the view, which needs
         * transformed to 'FAIL' for posting to the API.
         */
        if ('failure' == strtolower($type)) {
            return 'FAIL';
        }

        return strtoupper($type);
    }
}
