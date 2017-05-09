<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Core\Action\ViewActionResult;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\PersonProfileBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesGroupViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesViewModel;
use DvsaClient\Mapper\AnnualAssessmentCertificatesMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class AnnualAssessmentCertificatesAction implements AutoWireableInterface
{
    private $personProfileUrlGenerator;

    /** @var PersonProfileBreadcrumbs */
    private $personProfileBreadcrumbs;

    /** @var AnnualAssessmentCertificatesMapper */
    private $annualAssessmentCertificatesMapper;

    /** @var AnnualAssessmentCertificatesRoutes */
    private $annualAssessmentCertificatesRoutes;

    /** @var AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';

    public function __construct(
        PersonProfileBreadcrumbs $personProfileBreadcrumbs,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        AnnualAssessmentCertificatesMapper $annualAssessmentCertificatesMapper,
        AnnualAssessmentCertificatesRoutes $annualAssessmentCertificatesRoutes,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions
    ) {
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->personProfileBreadcrumbs = $personProfileBreadcrumbs;
        $this->annualAssessmentCertificatesMapper = $annualAssessmentCertificatesMapper;
        $this->annualAssessmentCertificatesRoutes = $annualAssessmentCertificatesRoutes;
        $this->certificatesPermissions = $certificatesPermissions;
    }

    public function execute(FormContext $formContext, AbstractAuthActionController $controller)
    {
        return $this->buildActionResult($formContext, $controller);
    }

    private function buildActionResult(FormContext $formContext, AbstractAuthActionController $controller)
    {
        $isUserViewingHisOwnProfile = $this->isUserViewingHisOwnProfile($formContext);
        $vm = new AnnualAssessmentCertificatesViewModel(
            $this->getPageSubtitle($isUserViewingHisOwnProfile),
            $this->getPreviousUrl(),
            $this->getGroupViewModel($formContext, 'A'),
            $this->getAddLinkForGroup($controller, 'A'),
            $this->getGroupViewModel($formContext, 'B'),
            $this->getAddLinkForGroup($controller, 'B'),
            $this->certificatesPermissions->isGrantedCreate(
                $formContext->getTargetPersonId(),
                $formContext->getLoggedInPersonId()
            ),
            $isUserViewingHisOwnProfile
        );

        $breadcrumbs = $this->personProfileBreadcrumbs->getBreadcrumbs($formContext->getTargetPersonId(), $controller,
            'Annual assessment certificates');

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate($vm->getTemplate());

        $actionResult->layout()->setPageTitle($vm->getPageTitle());
        $actionResult->layout()->setPageSubTitle($vm->getPageSubtitle());

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);

        return $actionResult;
    }

    private function getPreviousUrl()
    {
        return $this->personProfileUrlGenerator->toPersonProfile();
    }

    private function getGroupViewModel(FormContext $context, $group)
    {
        $personId = $context->getTargetPersonId();
        $annualAssessmentCertificates = $this->annualAssessmentCertificatesMapper->getAnnualAssessmentCertificates($personId, $group);

        return new AnnualAssessmentCertificatesGroupViewModel(
            $annualAssessmentCertificates,
            $context,
            $this->annualAssessmentCertificatesRoutes,
            $group,
            $this->certificatesPermissions
        );
    }

    private function getPageSubtitle($isPersonViewingItsOwnProfile)
    {
        return $isPersonViewingItsOwnProfile ? self::SUBTITLE_YOUR_PROFILE : self::SUBTITLE_USER_PROFILE;
    }

    private function getAddLinkForGroup(AbstractAuthActionController $controller, $group)
    {
        return $controller->url()->fromRoute($this->annualAssessmentCertificatesRoutes->getAddRoute(),
            $controller->params()->fromRoute() + ['group' => $group]
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
