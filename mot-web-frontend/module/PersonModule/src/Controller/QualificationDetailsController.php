<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Core\Action\ActionResult;
use Core\Controller\AbstractAuthActionController;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\ReviewStepAction;
use Doctrine\DBAL\Schema\View;
use Dvsa\Mot\Frontend\PersonModule\Action\QualificationDetailsAction;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Model\QualificationDetailsAddProcess;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Model\QualificationDetailsAbstractProcess;
use Dvsa\Mot\Frontend\PersonModule\Model\QualificationDetailsEditProcess;
use Dvsa\Mot\Frontend\PersonModule\Service\RemoveCertificateDetailsService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Model\ViewModel;
use Core\TwoStepForm\ConfirmationStepAction;

class QualificationDetailsController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $viewAction;
    private $editStepAction;
    private $reviewStepAction;
    private $confirmationStepAction;
    private $qualificationDetailsAddProcess;
    private $qualificationDetailsEditProcess;

    private $removeCertificateDetailsService;

    private $breadcrumbs;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    public function __construct(
        QualificationDetailsAction $viewAction,
        EditStepAction $editStepAction,
        ReviewStepAction $reviewStepAction,
        ConfirmationStepAction $confirmationStepAction,
        QualificationDetailsAddProcess $qualificationDetailsAddProcess,
        QualificationDetailsEditProcess $qualificationDetailsEditProcess,
        RemoveCertificateDetailsService $removeCertificateDetailsService,
        CertificatesBreadcrumbs $breadcrumbs,
        ContextProvider $contextProvider
    )
    {
        $this->viewAction = $viewAction;
        $this->editStepAction = $editStepAction;
        $this->reviewStepAction = $reviewStepAction;
        $this->confirmationStepAction = $confirmationStepAction;
        $this->qualificationDetailsAddProcess = $qualificationDetailsAddProcess;
        $this->qualificationDetailsEditProcess = $qualificationDetailsEditProcess;
        $this->removeCertificateDetailsService = $removeCertificateDetailsService;
        $this->breadcrumbs = $breadcrumbs;
        $this->contextProvider = $contextProvider;
    }

    public function viewAction()
    {
        $personId = $this->getPersonId();

        $actionResult = $this->viewAction->execute($personId, $this);

        return $this->applyActionResult($actionResult);
    }

    private function runQualificationDetailsProcessAction(
        QualificationDetailsAbstractProcess $process, $action, $template)
    {
        $isPost = $this->getRequest()->isPost();
        $context = new FormContext(
            $this->getPersonId(),
            $this->getIdentity()->getUserId(),
            $this->params()->fromRoute('group'),
            $this
        );
        $formUuid = $this->params()->fromRoute('formUuid');
        if(empty($formUuid)) {
            $formUuid = $this->params()->fromQuery('formUuid');
        }

        //todo eh
        if(get_class($action) == EditStepAction::class) {
            $formData = $this->getRequest()->getPost()->getArrayCopy();
            $actionResult = $action->execute($isPost, $process, $context, $formUuid, $formData);
        } elseif(get_class($action) == ReviewStepAction::class) {
            $actionResult = $action->execute($isPost, $process, $context, $formUuid);
        } elseif(get_class($action) == ConfirmationStepAction::class) {
            $actionResult = $action->execute($isPost, $process, $context);
        }

        //todo if there's no redirect
        if(get_class($actionResult) == ActionResult::class) {
            $actionResult->setTemplate($template);
        }

        return $this->applyActionResult($actionResult);

    }

    public function addAction()
    {
        return $this->runQualificationDetailsProcessAction($this->qualificationDetailsAddProcess,
            $this->editStepAction, 'qualification-details/add-or-edit'
        );
    }

    public function addReviewAction()
    {
        return $this->runQualificationDetailsProcessAction($this->qualificationDetailsAddProcess,
            $this->reviewStepAction, 'qualification-details/review'
        );
    }

    public function addConfirmationAction()
    {
        return $this->runQualificationDetailsProcessAction($this->qualificationDetailsAddProcess,
            $this->confirmationStepAction, 'qualification-details/confirmation'
        );
    }

    public function editAction()
    {
        return $this->runQualificationDetailsProcessAction($this->qualificationDetailsEditProcess,
            $this->editStepAction, 'qualification-details/add-or-edit'
        );
    }

    public function editReviewAction()
    {
        return $this->runQualificationDetailsProcessAction($this->qualificationDetailsEditProcess,
            $this->reviewStepAction, 'qualification-details/review'
        );
    }

    public function removeAction()
    {
        $personId = $this->getPersonId();
        $group = $this->params()->fromRoute('group');

        $breadcrumbs = $this->breadcrumbs->getBreadcrumbsForQualificationDetails(
            $personId,
            $this,
            "Remove certificate");

        $this->removeCertificateDetailsService->setBreadcrumbs($breadcrumbs);

        $result = $this->removeCertificateDetailsService->process(
            $personId,
            $group,
            $this->breadcrumbs->getRouteForData(CertificatesBreadcrumbs::ROUTE_QUALIFICATION_DETAILS),
            $this->getRequest()->isPost()
        );

        return $this->applyActionResult($result);
    }

    /**
     * @return int
     */
    private function getPersonId()
    {
        $context = $this->contextProvider->getContext();

        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int) $this->params()->fromRoute('id');
    }
}
