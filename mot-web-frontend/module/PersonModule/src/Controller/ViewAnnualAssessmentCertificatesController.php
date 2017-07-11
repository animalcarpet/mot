<?php

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Core\BackLink\BackLinkQueryParam;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\AnnualAssessmentCertificatesAction;
use Dvsa\Mot\Frontend\PersonModule\Action\VtsTestersAnnualAssessmentCertificatesAction;
use Dvsa\Mot\Frontend\PersonModule\Model\ViewAnnualAssessmentCertificatesFormContext;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class ViewAnnualAssessmentCertificatesController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $viewAction;

    private $vtsTestersViewAction;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /** @var AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    public function __construct(
        AnnualAssessmentCertificatesAction $viewAction,
        VtsTestersAnnualAssessmentCertificatesAction $vtsTestersViewAction,
        ContextProvider $contextProvider,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions
    ) {
        $this->viewAction = $viewAction;
        $this->vtsTestersViewAction = $vtsTestersViewAction;
        $this->contextProvider = $contextProvider;
        $this->certificatesPermissions = $certificatesPermissions;
    }

    public function viewAction()
    {
        $context = $this->contextProvider->getContext();
        $personId = $this->getPersonId($context);

        $formContext = new ViewAnnualAssessmentCertificatesFormContext(
            $personId,
            $this->getIdentity()->getUserId(),
            $this,
            $this->params()->fromRoute("vehicleTestingStationId")
        );

        $backTo = $this->params()->fromQuery("backTo");

        if ($backTo === BackLinkQueryParam::VTS_TESTER_ASSESSMENTS || $backTo === BackLinkQueryParam::SERVICE_REPORTS) {
            $actionResult = $this->vtsTestersViewAction->execute($formContext, $this, $backTo);
        } else {
            $actionResult = $this->viewAction->execute($formContext, $this);
        }

        return $this->applyActionResult($actionResult);
    }

    /**
     * @param $context
     *
     * @return int
     */
    private function getPersonId($context)
    {
        return $context === ContextProvider::YOUR_PROFILE_CONTEXT ?
            $this->getIdentity()->getUserId() : (int) $this->params()->fromRoute('id');
    }
}
