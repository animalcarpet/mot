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
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectSentenceCaseConverter;

class AddDefectController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';

    const DEFECT_TYPE_ADVISORY = 'advisory';
    const DEFECT_TYPE_PRS = 'prs';
    const DEFECT_TYPE_FAILURE = 'failure';

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
     * AddDefectController constructor.
     *
     * @param DefectsJourneyUrlGenerator    $defectsJourneyUrlGenerator
     * @param DefectsJourneyContextProvider $defectsJourneyContextProvider
     */
    public function __construct(
        DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator,
        DefectsJourneyContextProvider $defectsJourneyContextProvider
    ) {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
        $this->defectsJourneyContextProvider = $defectsJourneyContextProvider;
    }

    /**
     * Handles the screen for adding a defect.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/defect?l1=0&l2=0&l3=undefined&l4=undefined&rfrIndex=0&type=Advisory
     *
     * @return ViewModel | Response
     */
    public function addAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
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

            /*
             * If the defect is added as an advisory, then display the defect's
             * advisory text on the Add Defect page.
             *
             * Else, if the defect isn't added as an advisory, then display the
             * defect's description on the Add Defect page.
             */
            $type === self::DEFECT_TYPE_ADVISORY ?
                $defectDetail = $defect->getAdvisoryText() : $defectDetail = $defect->getDescription();
            $defectDetailWithAcronymsExpanded = ucfirst(trim(DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($defectDetail)));

            $this->enableGdsLayout($title, '');
            $this->layout()->setVariable('pageTertiaryTitle', $defectDetailWithAcronymsExpanded);

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
                    'comment' => trim($comment),
                    'failureDangerous' => $failureDangerous,
                ];

                $motTestDefectId = $this->getRestClient()->post($apiPath, $data);
                if (!empty($motTestDefectId)) {
                    $this->addSuccessMessage(sprintf(
                        '<strong>This %s has been added:</strong><br> %s',
                        $type,
                        $defectDetail
                    ));

                    return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
                }
            }
        } catch (DefectTypeNotFoundException $e) {
            return $this->notFoundAction();
        } catch (RestApplicationException $e) {
            $errorMessages = $e->getErrors()[0];
        }

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $title);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $backUrl = $this->defectsJourneyUrlGenerator->goBack();

        return $this->createViewModel('defects/add-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'defectId' => $defectId,
            'type' => $type,
            'breadcrumbs' => $breadcrumbs,
            'errorMessages' => $errorMessages,
            'isManualAdvisory' => false,
            'backUrl' => $backUrl,
            'context' => $this->defectsJourneyContextProvider->getContextForBackUrlText(),
        ]);
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
     * @throws DefectTypeNotFoundException
     *
     * @return string
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
        /*
         * Defect type 'failure' is displayed in the view, which needs
         * transformed to 'FAIL' for posting to the API.
         */
        if ('failure' == strtolower($type)) {
            return 'FAIL';
        }

        return strtoupper($type);
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
