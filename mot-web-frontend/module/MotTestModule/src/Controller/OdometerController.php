<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use DateTime;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use DvsaMotTest\Model\OdometerUpdate;
use Zend\View\Model\ViewModel;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use Zend\Http\Response;
use Zend\Session\Container;

class OdometerController extends AbstractDvsaMotTestController
{
    const CONTENT_HEADER_TYPE__TRAINING_TEST = 'Training test';
    const CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION = 'MOT test reinspection';
    const CONTENT_HEADER_TYPE__MOT_TEST_RESULTS = 'MOT test results';
    const CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS = 'Non-MOT test';

    const ODOMETER_VALUE_REQUIRED_MESSAGE = "Odometer value must be entered to update odometer reading";
    const ODOMETER_FORM_ERROR_MESSAGE = "The odometer reading should be a valid number between 0 and 999,999";
    const ODOMETER_FORM_SUCCESS_MESSAGE = "The odometer reading has been updated";

    public function indexAction()
    {
        $motTestNumber = (int)$this->params('tID');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $odometerForm = $this->getForm(new OdometerUpdate());
            $odometerForm->setData($request->getPost());

            if ($odometerForm->isValid()) {
                try {
                    $validatedData = $odometerForm->getData();
                    $readingResultType = $validatedData['resultType'];
                    if ($readingResultType === OdometerReadingResultType::OK) {
                        if (!isset($validatedData['odometer']) || trim($validatedData['odometer']) === '') {
                            $this->addErrorMessages(self::ODOMETER_VALUE_REQUIRED_MESSAGE);
                            return $this->redirect()->toUrl($this->url()->fromRoute('odometer', ['tID' => $motTestNumber]));
                        }
                        $data = [
                            'value' => (int)$validatedData['odometer'],
                            'unit' => $validatedData['unit'],
                            'resultType' => $readingResultType
                        ];
                    } else {
                        $data = ['resultType' => $readingResultType];
                    }

                    $apiUrl = MotTestUrlBuilder::odometerReading($motTestNumber)->toString();
                    $this->getRestClient()->put($apiUrl, $data);
                } catch (RestApplicationException $e) {
                    $this->addErrorMessages($e->getDisplayMessages());
                }

                $this->addSuccessMessage(self::ODOMETER_FORM_SUCCESS_MESSAGE);
                return $this->redirect()->toRoute('mot-test', ['motTestNumber' => $motTestNumber]);
            } else {
                $this->addErrorMessages(self::ODOMETER_FORM_ERROR_MESSAGE);
                return $this->redirect()->toRoute('odometer', ['tID' => $motTestNumber]);
            }
        }

        $isDemo = false;
        $isReinspection = false;
        $isNonMotTest = false;

        /** @var MotTestDto $motTest */
        $motTest = null;

        try {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            $testType = $motTest->getTestType();
            $isDemo = MotTestType::isDemo($testType->getCode());
            $isReinspection = MotTestType::isReinspection($testType->getCode());
            $isNonMotTest = MotTestType::isNonMotTypes($testType->getCode());
        } catch (ValidationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        $this->layout('layout/layout-govuk.phtml');

        $breadcrumbs = $this->getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $this->layout()->setVariable('pageTitle', 'Odometer reading');

        return $this->createViewModel('mot-test/index.twig', [
            'motTest' => $motTest,
            'vehicle' => $motTest->getVehicle(),
            'vehicleMakeAndModel' => ucwords($motTest->getVehicle()->getMakeAndModel()),
            'vehicleFirstUsedDate' => DateTime::createFromFormat('Y-m-d', $motTest->getVehicle()->getFirstUsedDate())->format('j M Y'),
            'isDemo' => $isDemo,
        ]);
    }

    /**
     * Get the breadcrumbs given the context of the url.
     *
     * @param boolean $isDemo
     * @param boolean $isReinspection
     * @param boolean $isNonMotTest
     *
     * @return array
     */
    private function getBreadcrumbs($isDemo, $isReinspection, $isNonMotTest)
    {
        $breadcrumbs = [];

        $tid = $this->params()->fromRoute('tID');
        $motTestResultsUrl = $this->url()->fromRoute('mot-test', ['motTestNumber' => $tid]);

        if ($isDemo) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__TRAINING_TEST => $motTestResultsUrl];
        } elseif ($isReinspection) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_REINSPECTION => $motTestResultsUrl];
        } elseif ($isNonMotTest) {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__NON_MOT_TEST_RESULTS => $motTestResultsUrl];
        } else {
            $breadcrumbs += [self::CONTENT_HEADER_TYPE__MOT_TEST_RESULTS => $motTestResultsUrl];
        }

        $breadcrumbs += ['Odometer reading' => ''];

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