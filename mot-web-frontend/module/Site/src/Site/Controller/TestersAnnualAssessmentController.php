<?php

namespace Site\Controller;

use Core\Controller\AbstractDvsaActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\Action\TestersAnnualAssessmentAction;

class TestersAnnualAssessmentController extends AbstractDvsaActionController implements AutoWireableInterface
{
    private $testersAnnualAssessmentAction;

    public function __construct(TestersAnnualAssessmentAction $testersAnnualAssessmentAction)
    {
        $this->testersAnnualAssessmentAction = $testersAnnualAssessmentAction;
    }

    public function testersAnnualAssessmentAction()
    {
        $this->setHeadTitle("Tester annual assessments");
        $backTo = $this->params()->fromQuery("backTo");

        return $this->applyActionResult(
            $this->testersAnnualAssessmentAction->annualAssessmentCertificatesAction($this->params('id'), $backTo)
        );
    }
}