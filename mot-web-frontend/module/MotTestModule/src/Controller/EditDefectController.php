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
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\View\Model\ViewModel;

class EditDefectController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__ADD_DEFECT = "Add a defect";

    const DEFECT_TYPE_ADVISORY = 'advisory';
    const DEFECT_TYPE_PRS = 'prs';
    const DEFECT_TYPE_FAILURE = 'failure';

    /**
     * @var DefectsJourneyContextProvider
     */
    private $defectsJourneyContextProvider;

    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * EditDefectController constructor.
     *
     * @param DefectsJourneyContextProvider $defectsJourneyContextProvider
     * @param DefectsJourneyUrlGenerator    $defectsJourneyUrlGenerator
     */
    public function __construct(
        DefectsJourneyContextProvider $defectsJourneyContextProvider,
        DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator
    ) {
        $this->defectsJourneyContextProvider = $defectsJourneyContextProvider;
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
    }

    /**
     * Handles the screen for editing a defect.
     *
     * Heroku screen not available yet.
     *
     * @throws DefectTypeNotFoundException
     * @throws UnauthorisedException
     * @throws \Dvsa\Mot\Frontend\MotTestModule\Exception\RouteNotAllowedInContextException
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $defectId = (int) $this->params()->fromRoute('defectItemId');

        $isReinspection = null;
        $isDemoTest = null;
        /** @var MotTestDto $motTest */
        $motTest = null;
        $title = '';
        $errorMessages = [];
        $type = '';
        $identifiedDefect = null;

        $backUrl = $this->defectsJourneyUrlGenerator->goBack();

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);

            $identifiedDefect = IdentifiedDefectCollection::fromMotApiData($motTest)->getDefectById($defectId);

            $type = $identifiedDefect->getDefectType();

            $title = $this->createTitle($type);
            $this->enableGdsLayout($title, '');
            $this->layout()->setVariable('pageTertiaryTitle', $identifiedDefect->getName());

            $request = $this->getRequest();
            if ($request->isPost()) {
                $apiPath = MotTestUrlBuilder::motTestRfr($motTest->getMotTestNumber());

                $locationLateral = $request->getPost('locationLateral');
                $locationLongitudinal = $request->getPost('locationLongitudinal');
                $locationVertical = $request->getPost('locationVertical');
                $comment = trim($request->getPost('comment'));
                $failureDangerous = $request->getPost('failureDangerous') ? true : false;

                // Data to be sent to the API to edit a defect.
                $data = [
                    'id' => $defectId,
                    'locationLateral' => ($locationLateral !== 'n/a') ? $locationLateral : null,
                    'locationLongitudinal' => ($locationLongitudinal !== 'n/a') ? $locationLongitudinal : null,
                    'locationVertical' => ($locationVertical !== 'n/a') ? $locationVertical : null,
                    'comment' => $comment,
                    'failureDangerous' => $failureDangerous,
                ];

                $this->getRestClient()->postJson($apiPath, $data);
                $this->addSuccessMessage(sprintf(
                    '<strong>This %s has been edited:</strong><br> %s',
                    $type,
                    $identifiedDefect->isManualAdvisory() ? $comment : $identifiedDefect->getName()
                ));

                return $this->redirect()->toUrl($backUrl);
            }

            $testType = $motTest->getTestType();
            $isDemoTest = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
        } catch (RestApplicationException $e) {
            $errorMessages = $e->getErrors()[0];
        } catch (RestServiceUnexpectedContentTypeException $e) {
            // On error from API, show error message in flash messenger.
            $this->addErrorMessage(sprintf(
                'The %s could not be edited. Please try again.',
                $type
            ));
        }

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $title);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->createViewModel('defects/edit-defect.twig', [
            'type' => $type,
            'errorMessages' => $errorMessages,
            'identifiedDefect' => $identifiedDefect,
            'context' => $this->defectsJourneyContextProvider->getContextForBackUrlText(),
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
                $title = 'Edit advisory';
                break;
            case self::DEFECT_TYPE_PRS:
                $title = 'Edit PRS';
                break;
            case self::DEFECT_TYPE_FAILURE:
                $title = 'Edit failure';
                break;
            default:
                throw new DefectTypeNotFoundException();
        }

        return $title;
    }

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param bool   $isDemo
     * @param bool   $isReinspection
     * @param string $title
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $title)
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

        $defectsJourneyContext = $this->defectsJourneyContextProvider->getContext();

        /* Get breadcrumbs for the MOT Test Results page context
           (Get correct breadcrumbs if on the MOT Test Results page) */
        if (DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT === $defectsJourneyContext) {
            $breadcrumbs += [$title => ''];
        }
        // Get breadcrumbs for any other context in the Defect Journey (e.g. browse, browse-root).
        else {
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__ADD_DEFECT => $this->defectsJourneyUrlGenerator->goBack(),
            ];
            $breadcrumbs += [$title => ''];
        }

        return $breadcrumbs;
    }
}
