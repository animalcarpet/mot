<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Application\Data\ApiPersonalDetails;
use Core\Action\ViewActionResult;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\PersonProfileBreadcrumbs;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Routes\QualificationDetailsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsGroupViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsViewModel;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;

class QualificationDetailsAction extends AbstractAuthActionController implements AutoWireableInterface
{
    private $personalDetailsService;
    private $personProfileUrlGenerator;
    private $contextProvider;
    private $personProfileGuardBuilder;
    private $qualificationDetailsMapper;
    private $qualificationDetailsRoutes;
    private $personProfileBreadcrumbs;

    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';
    const ROUTE_PARAM_GROUP = 'group';

    public function __construct(
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        QualificationDetailsMapper $qualificationDetailsMapper,
        QualificationDetailsRoutes $qualificationDetailsRoutes,
        PersonProfileBreadcrumbs $personProfileBreadcrumbs,
        ContextProvider $contextProvider,
        ApiPersonalDetails $personalDetailsService
    ) {
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->qualificationDetailsMapper = $qualificationDetailsMapper;
        $this->qualificationDetailsRoutes = $qualificationDetailsRoutes;
        $this->personProfileBreadcrumbs = $personProfileBreadcrumbs;
        $this->contextProvider = $contextProvider;
        $this->personalDetailsService = $personalDetailsService;
    }

    public function execute($personId, AbstractAuthActionController $controller)
    {
        $context = $this->contextProvider->getContext();

        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($personId);
        $personalDetails = new PersonalDetails($personalDetailsData);
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        if (!$personProfileGuard->canViewQualificationDetails()) {
            throw new UnauthorisedException('No identity provided');
        }

        return $this->buildActionResult($personId, $context, $controller, $personProfileGuard);
    }

    private function buildActionResult($personId, $context, $controller, PersonProfileGuard $personProfileGuard
    ) {
        $testerAuthorisation = $this->personProfileGuardBuilder->getTesterAuthorisation($personId);

        $vm = new QualificationDetailsViewModel(
            $this->getPreviousUrl(),
            $this->getPageSubtitle($context),
            $this->getGroupViewModel($personId, $testerAuthorisation->getGroupAStatus(),
                VehicleClassGroupCode::BIKES, $controller, $personProfileGuard),
            $this->getGroupViewModel($personId, $testerAuthorisation->getGroupBStatus(),
                VehicleClassGroupCode::CARS_ETC, $controller, $personProfileGuard),
            $this->isGuidanceShown(
                VehicleClassGroupCode::BIKES,
                VehicleClassGroupCode::CARS_ETC,
                $personProfileGuard
                )
        );

        $breadcrumbs = $this->personProfileBreadcrumbs->getBreadcrumbs($personId, $controller, $vm->getPageTitle());

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

    private function getPageSubtitle($context)
    {
        return ContextProvider::YOUR_PROFILE_CONTEXT === $context ? self::SUBTITLE_YOUR_PROFILE :
            self::SUBTITLE_USER_PROFILE;
    }

    private function isGuidanceShown($vehicleClassGroupACode,
                                     $vehicleClassGroupBCode,
                                     PersonProfileGuard $personProfileGuard)
    {
        return $personProfileGuard->canViewGuidance($vehicleClassGroupACode, $vehicleClassGroupBCode);
    }

    private function getGroupViewModel($personId, $status, $group, AbstractAuthActionController $controller, PersonProfileGuard $personProfileGuard)
    {
        try {
            $qualificationDetails = $this->qualificationDetailsMapper->getQualificationDetails($personId, $group);
        } catch (NotFoundException $e) {
            $qualificationDetails = null;
        }

        $changeUrl = null;
        $addUrl = null;
        $removeUrl = null;

        if (!empty($qualificationDetails)) {
            if ($personProfileGuard->canUpdateQualificationDetails($group)) {
                $changeUrl = $controller->url()->fromRoute($this->qualificationDetailsRoutes->getEditRoute(),
                    $controller->params()->fromRoute() + [self::ROUTE_PARAM_GROUP => strtolower($group)]
                );
            }
        }

        if ($personProfileGuard->canCreateQualificationDetails($group)) {
            $addUrl = $controller->url()->fromRoute($this->qualificationDetailsRoutes->getAddRoute(),
                $controller->params()->fromRoute() + [self::ROUTE_PARAM_GROUP => strtolower($group)]
            );
        }

        if ($personProfileGuard->canRemoveQualificationDetails($group)) {
            $removeUrl = $controller->url()->fromRoute($this->qualificationDetailsRoutes->getRemoveRoute(),
                $controller->params()->fromRoute() + [self::ROUTE_PARAM_GROUP => strtolower($group)]
            );
        }

        return new QualificationDetailsGroupViewModel($group, $status, $qualificationDetails, $changeUrl, $addUrl,
            $removeUrl
        );
    }
}
