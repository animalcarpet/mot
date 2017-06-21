<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Core\Action\ViewActionResult;
use Core\Controller\AbstractAuthActionController;
use Core\Routing\VtsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Model\ViewAnnualAssessmentCertificatesFormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesGroupViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesViewModel;
use DvsaClient\Mapper\AnnualAssessmentCertificatesMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VtsTestersAnnualAssessmentCertificatesAction implements AutoWireableInterface
{
    private $personProfileUrlGenerator;

    /** @var CertificatesBreadcrumbs */
    private $certificatesBreadcrumbs;

    /** @var AnnualAssessmentCertificatesMapper */
    private $annualAssessmentCertificatesMapper;

    /** @var AnnualAssessmentCertificatesRoutes */
    private $annualAssessmentCertificatesRoutes;

    /** @var AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';

    public function __construct(
        CertificatesBreadcrumbs $certificatesBreadcrumbs,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        AnnualAssessmentCertificatesMapper $annualAssessmentCertificatesMapper,
        AnnualAssessmentCertificatesRoutes $annualAssessmentCertificatesRoutes,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions
    ) {
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->certificatesBreadcrumbs = $certificatesBreadcrumbs;
        $this->annualAssessmentCertificatesMapper = $annualAssessmentCertificatesMapper;
        $this->annualAssessmentCertificatesRoutes = $annualAssessmentCertificatesRoutes;
        $this->certificatesPermissions = $certificatesPermissions;
    }

    public function execute(ViewAnnualAssessmentCertificatesFormContext $formContext, AbstractAuthActionController $controller)
    {
        return $this->buildActionResult($formContext, $controller);
    }

    private function buildActionResult(ViewAnnualAssessmentCertificatesFormContext $formContext, AbstractAuthActionController $controller)
    {
        $isUserViewingHisOwnProfile = $this->isUserViewingHisOwnProfile($formContext);
        $breadcrumbs = $this->certificatesBreadcrumbs->getBreadcrumbsForVtsAnnualAssessmentCertificate(
            $formContext->getTargetPersonId(),
            $controller
        );
        $addLinkQueryParams = ["backTo" => "vts-tester-assessments"];


        $vm = new AnnualAssessmentCertificatesViewModel(
            $this->getLastBreadcrumbLabel($breadcrumbs),
            "Annual assessment certificates",
            VtsRoutes::of($controller->url())->vtsTestersAnnualAssessment($formContext->getSiteId(), $addLinkQueryParams),
            sprintf("Return to %s", "testers annual assessment"),
            $this->getGroupViewModel($formContext, 'A'),
            $this->getAddLinkForGroup($controller, 'A', $addLinkQueryParams),
            $this->getGroupViewModel($formContext, 'B'),
            $this->getAddLinkForGroup($controller, 'B', $addLinkQueryParams),
            $this->certificatesPermissions->isGrantedCreate(
                $formContext->getTargetPersonId(),
                $formContext->getLoggedInPersonId()
            ),
            $isUserViewingHisOwnProfile
        );

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate($vm->getTemplate());

        $actionResult->layout()->setPageTitle($vm->getPageTitle());
        $actionResult->layout()->setPageSubTitle($vm->getPageSubtitle());

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);

        return $actionResult;
    }

    private function getGroupViewModel(ViewAnnualAssessmentCertificatesFormContext $context, $group)
    {
        $personId = $context->getTargetPersonId();
        $siteId = $context->getSiteId();
        $annualAssessmentCertificates = $this->annualAssessmentCertificatesMapper->getAnnualAssessmentCertificates($personId, $group, $siteId);

        return new AnnualAssessmentCertificatesGroupViewModel(
            $annualAssessmentCertificates,
            $context,
            $this->annualAssessmentCertificatesRoutes,
            $group,
            $this->certificatesPermissions
        );
    }

    /**
     * @param array $breadcrumbs
     * @return string
     */
    private function getLastBreadcrumbLabel(array $breadcrumbs)
    {
        end($breadcrumbs);
        return key($breadcrumbs);
    }

    private function getAddLinkForGroup(AbstractAuthActionController $controller, $group, array $queryParams = [])
    {
        return $controller->url()->fromRoute($this->annualAssessmentCertificatesRoutes->getAddRoute(),
            $controller->params()->fromRoute() + ['group' => $group],
            ["query" => $queryParams]
        );
    }

    /**
     * @param FormContext $formContext
     *
     * @return bool
     */
    private function isUserViewingHisOwnProfile(FormContext $formContext)
    {
        return $formContext->getLoggedInPersonId() === $formContext->getTargetPersonId();
    }
}
