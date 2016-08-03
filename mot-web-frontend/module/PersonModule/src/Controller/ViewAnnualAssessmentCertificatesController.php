<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;


use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\AnnualAssessmentCertificatesAction;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class ViewAnnualAssessmentCertificatesController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $viewAction;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /** @var AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    public function __construct(
        AnnualAssessmentCertificatesAction $viewAction,
        ContextProvider $contextProvider,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions
    ) {
        $this->viewAction = $viewAction;
        $this->contextProvider = $contextProvider;
        $this->certificatesPermissions = $certificatesPermissions;
    }

    public function viewAction()
    {
        $context = $this->contextProvider->getContext();
        $personId = $this->getPersonId($context);

        $formContext = new FormContext(
            $personId,
            $this->getIdentity()->getUserId(),
            null,
            $this
        );

        $this->certificatesPermissions->assertGrantedView(
            $formContext->getTargetPersonId(),
            $formContext->getLoggedInPersonId()
        );

        $actionResult = $this->viewAction->execute($formContext, $this);

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