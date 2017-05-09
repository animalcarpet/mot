<?php

namespace DvsaMotEnforcement\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Zend\View\Model\ViewModel;

/**
 * Class ReinspectionReportController.
 */
class ReinspectionReportController extends AbstractAuthActionController
{
    const DATE_FORMAT = 'd M Y';
    const TIME_FORMAT = 'h:i';

    const PRINT_TYPE_REPORT = 'rpt';
    const PRINT_TYPE_ADVISORY_WARNING_LETTER = 'awl';
    const PRINT_TYPE_APPEAL = 'ia';

    const REINSPECTION_CONFIRMATION_PAGE_TITLE = 'Confirmation';
    const REINSPECTION_ASSESSMENT_DETAILS_SAVED_PAGE_TITLE = 'Assessment details saved';

    const REINSPECTION_NOT_SAVED =
        'The reinspection assessment outcome and details of the test differences have not been saved';

    /**
     * confirmation of saving reinspection assessment (rfr differences + mot tests).
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function recordAssessmentConfirmationAction()
    {
        //  --  check auth and PermissionInSystem
        $this->assertGranted(PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM);

        $this->layout('layout/layout-govuk.phtml');

        $breadcrumbs = [
            'Reinspection Differences' => '',
            'Assessment Details Confirmation' => '',
        ];

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $resultId = (int) $this->params()->fromRoute('resultId', 0);
        $apiUrl = UrlBuilder::enforcementMotTestResult($resultId)->toString();

        try {
            $reinspectionResult = $this->getRestClient()->get($apiUrl);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage(self::REINSPECTION_NOT_SAVED);
            $reinspectionResult = [];
        }

        $errorMessages = $this->flashMessenger()->getCurrentErrorMessages();

        if (count($errorMessages)) {
            $this->layout()->setVariable('pageSubTitle', 'Error');
            $this->layout()->setVariable('pageTitle', 'Assessment failed to save');
        } else {
            $this->layout()->setVariable('pageSubTitle', self::REINSPECTION_CONFIRMATION_PAGE_TITLE);
            $this->layout()->setVariable('pageTitle', self::REINSPECTION_ASSESSMENT_DETAILS_SAVED_PAGE_TITLE);
        }

        return new ViewModel(
            [
                'reinspectionResult' => $reinspectionResult,
                'errorMessages' => $errorMessages,
                'successMessages' => $this->flashMessenger()->getSuccessMessages(),
            ]
        );
    }
}
