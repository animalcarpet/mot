<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Defect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefect;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefectCollection;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
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
        $observedDefectId = (int) $this->params()->fromRoute('defectItemId');
        /** @var MotTestDto $motTest */
        $motTest = null;
        $isReinspection = false;
        $isDemoTest = false;

        $backUrl = $this->defectsJourneyUrlGenerator->goBack();

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemoTest = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $observedDefect = $this->getObservedDefect($observedDefectId, $motTest);

        /*
         * If we're making a POST request to this URL, i.e., we've clicked
         * on the remove <defectType> button, redirect to the screen from
         * which the user clicked "Remove" on the list of ObservedDefects.
         */
        if ($this->getRequest()->isPost()) {
            try {
                $this->disassociateObservedDefectFromMotTest($observedDefectId, $motTestNumber);

            /*
             * Add success message to the flash messenger on successful removal
             * of an ObservedDefect from an MOT test.
             */
            $this->addSuccessMessage(sprintf(
                '<strong>This %s has been removed:</strong><br> %s',
                $observedDefect->getDefectType(),
                $observedDefect->getName()
            ));

                return $this->redirect()->toUrl($backUrl);
            } catch (RestServiceUnexpectedContentTypeException $e) {
                /*
                 * On error from API, show error message in flash messenger.
                 */
                $this->addErrorMessage(sprintf(
                    'The %s could not be removed. Please try again.',
                    $observedDefect->getDefectType()
                ));
            }
        }

        $breadcrumbs = $this->getBreadcrumbs($isDemoTest, $isReinspection, $observedDefect->getDefectType());
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->enableGdsLayout('Remove '.$observedDefect->getDefectType(), '');

        return $this->createViewModel('defects/remove-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'observedDefect' => $observedDefect,
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
     * Get a specified ObservedDefect. Also fetch the defect breadcrumb (which
     * is displayed above the defect name on the Remove Defect form).
     *
     * @param int        $observedDefectId
     * @param MotTestDto $motTest
     *
     * @return ObservedDefect
     */
    private function getObservedDefect($observedDefectId, MotTestDto $motTest)
    {
        $observedDefect = ObservedDefectCollection::fromMotApiData($motTest)->getDefectById($observedDefectId);

        $breadcrumb = Defect::fromApi($this->restClient->get(
            MotTestUrlBuilder::reasonForRejection($motTest->getMotTestNumber(), $observedDefect->getDefectId())
        )['data'])->getDefectBreadcrumb();

        $observedDefect->setBreadcrumb($breadcrumb);

        return $observedDefect;
    }

    /**
     * Remove the ObservedDefect from the MOT test.
     *
     * @param int $observedDefectId
     * @param int $motTestNumber
     */
    private function disassociateObservedDefectFromMotTest($observedDefectId, $motTestNumber)
    {
        $this->getRestClient()->delete(MotTestUrlBuilder::reasonForRejection($motTestNumber, $observedDefectId));
    }

    /**
     * @param bool $isDemo
     * @param bool $isReinspection
     * @param $defectType
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $defectType)
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
        $breadcrumbs += ['Remove a '.$defectType => ''];

        return $breadcrumbs;
    }
}
