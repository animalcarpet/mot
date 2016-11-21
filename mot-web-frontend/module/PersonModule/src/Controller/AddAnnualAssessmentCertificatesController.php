<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;


use Core\Action\ViewActionResult;
use Core\Controller\AbstractAuthActionController;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\ReviewStepAction;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesAddProcess;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class AddAnnualAssessmentCertificatesController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $annualAssessmentCertificatesAddProcess;
    private $editStepAction;
    private $reviewStepAction;

    private $personId;
    /** @var  FormContext */
    private $context;
    private $isPost;
    private $formUuid;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    public function __construct(
        ContextProvider $contextProvider,
        AnnualAssessmentCertificatesAddProcess $annualAssessmentCertificatesAddProcess,
        EditStepAction $editStepAction,
        ReviewStepAction $reviewStepAction
    )
    {
        $this->contextProvider = $contextProvider;
        $this->annualAssessmentCertificatesAddProcess = $annualAssessmentCertificatesAddProcess;
        $this->editStepAction = $editStepAction;
        $this->reviewStepAction = $reviewStepAction;
    }

    public function addAction()
    {
        $template = 'annual-assessment-certificates/add';

        return $this->setParametersForProcessAction($this->editStepAction, $template);
    }

    public function addReviewAction()
    {
        $template = 'annual-assessment-certificates/add-review';

        return $this->setParametersForProcessAction($this->reviewStepAction, $template);
    }

    private function setParametersForProcessAction($action, $template)
    {
        $this->personId = $this->getPersonId($this->contextProvider->getContext());

        $this->context = new FormContext(
            $this->personId,
            $this->getIdentity()->getUserId(),
            $this->params()->fromRoute('group'),
            $this
        );

        $this->isPost = $this->getRequest()->isPost();

        $this->formUuid = $this->params()->fromRoute('formUuid');

        if (empty($this->formUuid)) {
            $this->formUuid = $this->params()->fromQuery('formUuid');

        }

        $actionResult = null;
        if ($action instanceof EditStepAction) {
            $formData = $this->getRequest()->getPost()->getArrayCopy();
            $actionResult = $action->execute(
                $this->isPost,
                $this->annualAssessmentCertificatesAddProcess,
                $this->context,
                $this->formUuid,
                $formData
            );
        } elseif ($action instanceof ReviewStepAction) {
            $actionResult = $action->execute(
                $this->isPost,
                $this->annualAssessmentCertificatesAddProcess,
                $this->context,
                $this->formUuid
            );
        }

        if ($actionResult instanceof ViewActionResult) {
            $actionResult->setTemplate($template);
        }

        return $this->applyActionResult($actionResult);
    }

    /**
     * @param $context
     * @return int
     */
    private function getPersonId($context)
    {
        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int)$this->params()->fromRoute('id');
    }
}