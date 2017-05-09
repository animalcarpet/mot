<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\FlashMessageBuilder;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\MotTestResults;

/**
 * Class RepairDefectController.
 */
class RepairDefectController extends AbstractDvsaMotTestController
{
    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * RepairDefectController constructor.
     *
     * @param DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator
     */
    public function __construct(DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator)
    {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
    }

    /**
     * @return Response
     */
    public function repairAction()
    {
        $request = $this->getRequest();
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $identifiedDefectId = (int) $this->params('identifiedDefectId');
        $identifiedDefectType = $request->getPost('defectType', 'defect');
        $identifiedDefectText = $request->getPost('defectText', '');
        $isAjax = $request->isXmlHttpRequest();

        try {
            $apiUrl = MotTestUrlBuilder::markDefectAsRepaired($motTestNumber, $identifiedDefectId)->toString();
            $this->getRestClient()->post($apiUrl);

            if (false === $isAjax) {
                $this->addSuccessMessage(FlashMessageBuilder::defectRepairedSuccessfully($identifiedDefectType, $identifiedDefectText));
            } else {
                $success = true;
            }
        } catch (GeneralRestException $e) {
            if (false === $isAjax) {
                $this->addErrorMessage(FlashMessageBuilder::defectRepairedUnsuccessfully($identifiedDefectType, $identifiedDefectText));
            } else {
                $success = false;
            }
        }

        if (true === $isAjax) {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            //Check if it is a retest and get the original mot test
            if (MotTestType::isRetest($motTest->getTestTypeCode())) {
                $originalMotTest = $this->getMotTestFromApi($motTest->getMotTestOriginalNumber());
                $motTestResults = new MotTestResults($motTest, $originalMotTest);
            } else {
                $motTestResults = new MotTestResults($motTest);
            }

            $data = array(
                'success' => $success,
                'defectType' => $identifiedDefectType,
                'action' => 'repair',
                'brakeTestOutcome' => $motTestResults->getBrakeTestOutcome(),
                'brakeTestResults' => $motTestResults->hasBrakeTestResult(),
                'brakesTested' => !$motTestResults->isBrakePerformanceNotTested(),
                'disableSubmitButton' => $motTestResults->shouldDisableSubmitButton(),
            );

            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(json_encode($data));

            return $response;
        } else {
            return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
        }
    }

    /**
     * @return Response
     */
    public function undoRepairAction()
    {
        $request = $this->getRequest();
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $identifiedDefectId = (int) $this->params('identifiedDefectId');
        $identifiedDefectType = $request->getPost('defectType', 'defect');
        $identifiedDefectText = $request->getPost('defectText', '');
        $isAjax = $request->isXmlHttpRequest();

        try {
            $apiUrl = MotTestUrlBuilder::undoMarkDefectAsRepaired($motTestNumber, $identifiedDefectId)->toString();

            $this->getRestClient()->post($apiUrl);

            if (false === $isAjax) {
                $this->addSuccessMessage(FlashMessageBuilder::undoDefectRepairSuccessfully($identifiedDefectType, $identifiedDefectText));
            } else {
                $success = true;
            }
        } catch (GeneralRestException $e) {
            if (false === $isAjax) {
                $this->addErrorMessage(FlashMessageBuilder::undoDefectRepairUnsuccessfully($identifiedDefectType, $identifiedDefectText));
            } else {
                $success = false;
            }
        }

        if (true === $isAjax) {
            $motTest = $this->getMotTestFromApi($motTestNumber);
            //Check if it is a retest and get the original mot test
            if (MotTestType::isRetest($motTest->getTestTypeCode())) {
                $originalMotTest = $this->getMotTestFromApi($motTest->getMotTestOriginalNumber());
                $motTestResults = new MotTestResults($motTest, $originalMotTest);
            } else {
                $motTestResults = new MotTestResults($motTest);
            }

            $data = array(
                'success' => $success,
                'defectType' => $identifiedDefectType,
                'action' => 'undo',
                'brakeTestOutcome' => $motTestResults->getBrakeTestOutcome(),
                'brakeTestResults' => $motTestResults->hasBrakeTestResult(),
                'brakesTested' => !$motTestResults->isBrakePerformanceNotTested(),
                'disableSubmitButton' => $motTestResults->shouldDisableSubmitButton(),
            );
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(json_encode($data));

            return $response;
        } else {
            return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
        }
    }
}
