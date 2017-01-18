<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\FlashMessageBuilder;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Exception\IdentifiedDefectNotFoundException;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class RemoveDefectController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS = 'Non-MOT test';
    const CONTENT_HEADER_TYPE__SEARCH_RESULTS = 'Search for a defect';
    const CONTENT_HEADER_TYPE__BROWSE_DEFECTS = 'Add a defect';

    /**
     * @var DefectsJourneyContextProvider
     */
    private $defectsJourneyContextProvider;

    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * RemoveDefectController constructor.
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
     * Handles the screen for removing a defect.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/removeDefect
     *
     * @return ViewModel | Response
     */
    public function removeAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $identifiedDefectId = (int) $this->params()->fromRoute('identifiedDefectId');
        /** @var MotTest $motTest */
        $motTest = null;
        $isReinspection = false;
        $isDemoTest = false;
        $isNonMotTest = false;

        $backUrl = $this->defectsJourneyUrlGenerator->goBack();

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestTypeCode();
            $isDemoTest = MotTestType::isDemo($testType);
            $isReinspection = MotTestType::isReinspection($testType);
            $isNonMotTest = MotTestType::isNonMotTypes($testType);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        try {
            $identifiedDefect = $this->getIdentifiedDefect($identifiedDefectId, $motTest);
        } catch (IdentifiedDefectNotFoundException $e) {
            return $this->redirect()->toUrl($backUrl);
        }

        /*
         * If we're making a POST request to this URL, i.e., we've clicked
         * on the remove <defectType> button, redirect to the screen from
         * which the user clicked "Remove" on the list of IdentifiedDefects.
         */
        if ($this->getRequest()->isPost()) {
            try {
                $this->disassociateIdentifiedDefectFromMotTest($identifiedDefectId, $motTestNumber);

            /*
             * Add success message to the flash messenger on successful removal
             * of an IdentifiedDefect from an MOT test.
             */
            $this->addSuccessMessage(FlashMessageBuilder::defectRemovedSuccessfully(
                $identifiedDefect->getDefectType(),
                $identifiedDefect->getName()
            ));

                return $this->redirect()->toUrl($backUrl);
            } catch (RestServiceUnexpectedContentTypeException $e) {
                /*
                 * On error from API, show error message in flash messenger.
                 */
                $this->addErrorMessage(sprintf(
                    'The %s could not be removed. Please try again.',
                    $identifiedDefect->getDefectType()
                ));
            }
        }

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $isNonMotTest, $identifiedDefect->getDefectType());
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->enableGdsLayout('Remove ' . $identifiedDefect->getDefectType(), '');

        return $this->createViewModel('defects/remove-defect.twig', [
            'motTestNumber' => $motTestNumber,
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
     * Get a specified IdentifiedDefect. Also fetch the defect breadcrumb (which
     * is displayed above the defect name on the Remove Defect form).
     *
     * @param int        $identifiedDefectId
     * @param MotTest $motTest
     *
     * @return IdentifiedDefect
     */
    private function getIdentifiedDefect($identifiedDefectId, MotTest $motTest)
    {
         return IdentifiedDefectCollection::fromMotApiData($motTest)->getDefectById($identifiedDefectId);
    }

    /**
     * Remove the IdentifiedDefect from the MOT test.
     *
     * @param int $identifiedDefectId
     * @param int $motTestNumber
     */
    private function disassociateIdentifiedDefectFromMotTest($identifiedDefectId, $motTestNumber)
    {
        $this->getRestClient()->delete(MotTestUrlBuilder::reasonForRejection($motTestNumber, $identifiedDefectId));
    }

    /**
     * @param bool $isDemo
     * @param bool $isReinspection
     * @param bool $isNonMotTest
     * @param $defectType
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest, $defectType)
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
            } elseif ($isNonMotTest) {
                // Non-MOT test
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS => $motTestResultsUrl,
                ];
            } else {
                // Normal test
                $breadcrumbs += [
                    self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl,
                ];
            }
        }

        if (DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT === $context) {
            // Browse defects context
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__BROWSE_DEFECTS => $this->defectsJourneyUrlGenerator->goBack(),
            ];
        } elseif (DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT === $context) {
            // Browse categories root context
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__BROWSE_DEFECTS => $this->defectsJourneyUrlGenerator->goBack(),
            ];
        } elseif (DefectsJourneyContextProvider::SEARCH_CONTEXT === $context) {
            // Search context
            $breadcrumbs += [
                self::CONTENT_HEADER_TYPE__SEARCH_RESULTS => $this->defectsJourneyUrlGenerator->goBack(),
            ];
        }
        $breadcrumbs += ['Remove ' . $defectType => ''];

        return $breadcrumbs;
    }
}
