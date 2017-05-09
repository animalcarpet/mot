<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Core\Action\ViewActionResult;
use Core\Controller\AbstractAuthActionController;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\ReviewStepAction;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesFormContext;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesRemoveProcess;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class RemoveAnnualAssessmentCertificatesController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $removeProcess;
    private $editStepAction;
    private $reviewStepAction;
    private $contextProvider;

    public function __construct(
        AnnualAssessmentCertificatesRemoveProcess $process,
        EditStepAction $editStepAction,
        ReviewStepAction $reviewStepAction,
        ContextProvider $contextProvider
    ) {
        $this->removeProcess = $process;
        $this->editStepAction = $editStepAction;
        $this->reviewStepAction = $reviewStepAction;
        $this->contextProvider = $contextProvider;
    }

    public function removeAction()
    {
        $formContext = new AnnualAssessmentCertificatesFormContext(
            $this->getViewedUserId(),
            $this->getIdentity()->getUserId(),
            $this->params()->fromRoute('group'),
            $this->params()->fromRoute('certificateId'),
            $this
        );

        $actionResult = $this->editStepAction->execute(
            $this->getRequest()->isPost(),
            $this->removeProcess,
            $formContext,
            $this->params()->fromQuery('formUuid')
        );

        if ($actionResult instanceof ViewActionResult) {
            $actionResult->setTemplate('annual-assessment-certificates/remove');
        }

        return $this->applyActionResult($actionResult);
    }

    private function getViewedUserId()
    {
        switch ($this->contextProvider->getContext()) {
            case ContextProvider::YOUR_PROFILE_CONTEXT:
                return $this->getIdentity()->getUserId();
            case ContextProvider::AE_CONTEXT:
            case ContextProvider::VTS_CONTEXT:
            case ContextProvider::USER_SEARCH_CONTEXT:
                return $this->params()->fromRoute('id');
        }
    }
}
