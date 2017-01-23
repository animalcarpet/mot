<?php
namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Request\Validator\Exception;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\QualificationDetailsBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Routes\QualificationDetailsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use DvsaClient\Mapper\SiteMapper;
use DvsaFeature\FeatureToggles;

class QualificationDetailsAddProcess extends QualificationDetailsAbstractProcess
{
    const GROUP_NAME_VIEW_VARIABLE = 'groupName';
    const CAN_ORDER_CARD_VIEW_VARIABLE = 'canOrderCard';
    const START_PAGE_ROUTE_VIEW_VARIABLE = 'startPageRoute';

    /**
     * @var AuthorisationService $authorisationService
     */
    private $authorisationService;

    private $twoFaFeatureToggle;

    public function __construct(
        QualificationDetailsMapper $qualificationDetailsMapper,
        SiteMapper $siteMapper,
        CertificatesBreadcrumbs $qualificationDetailsBreadcrumbs,
        ApiPersonalDetails $personalDetailsService,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        ContextProvider $contextProvider,
        QualificationDetailsRoutes $qualificationDetailsRoutes,
        AuthorisationService $authorisationService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    )
    {
        parent::__construct($qualificationDetailsMapper, $siteMapper, $qualificationDetailsBreadcrumbs,
            $personalDetailsService, $personProfileGuardBuilder, $contextProvider,
            $qualificationDetailsRoutes
        );

        $this->authorisationService = $authorisationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;

    }
    const QUERY_PARAM_FORM_UUID = 'formUuid';

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        return [];
    }

    public function getSuccessfulEditMessage()
    {
        return 'Group '.strtoupper($this->context->getGroup()).' certificate added successfully. '.
        'Qualification status has been changed to Demo test needed.';
    }

    /**
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToEditPage()
    {
        $route = $this->qualificationDetailsRoutes->getAddRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
        ];
        return new RedirectToRoute($route, $params);
    }

    /**
     * @param $formUuid
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToReviewPage($formUuid)
    {
        $route = $this->qualificationDetailsRoutes->getAddReviewRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
            self::ROUTE_PARAM_FORM_UUID => $formUuid,
        ];
        return new RedirectToRoute($route, $params);
    }

    public function redirectToConfirmationPage()
    {
        $route = $this->qualificationDetailsRoutes->getAddConfirmationRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
        ];
        return new RedirectToRoute($route, $params);
    }

    protected function getReviewStepBackUrl($formUuid)
    {
        $route = $this->qualificationDetailsRoutes->getAddRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
        ];

        return $this->context->getController()->url()->fromRoute($route, $params).'?'.http_build_query([
            self::QUERY_PARAM_FORM_UUID => $formUuid
        ]);
    }

    protected function getBreadcrumbCurrentStepName()
    {
        return 'Add certificate details';
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     * @return MotTestingCertificateDto
     */
    public function update($formData)
    {
        try {
            $this->qualificationDetailsMapper->getQualificationDetails(
                $this->context->getTargetPersonId(), $this->context->getGroup());

            $this->qualificationDetailsMapper->removeQualificationDetails($this->context->getTargetPersonId(),
                $this->context->getGroup());
        } catch( NotFoundException $e) {

        }

        return $this->qualificationDetailsMapper->createQualificationDetails($this->context->getTargetPersonId(),
            $this->context->getGroup(), $this->mapFormToDto($formData));
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        $personId = $this->context->getTargetPersonId();

        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($personId);
        $personalDetails = new PersonalDetails($personalDetailsData);
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            ContextProvider::YOUR_PROFILE_CONTEXT
        );

        return $personProfileGuard->canCreateQualificationDetails(strtoupper($this->context->getGroup()));
    }

    public function populateConfirmationPageVariables()
    {
        $variables = [];
        $variables[self::GROUP_NAME_VIEW_VARIABLE] = $this->context->getGroup();
        $variables[self::CAN_ORDER_CARD_VIEW_VARIABLE] = $this->shouldSeeOrderSecurityCard();
        $variables[self::START_PAGE_ROUTE_VIEW_VARIABLE] = $this->certificatesBreadcrumbs->getQualificationDetailsRoute();

        return $variables;
    }

    public function getEditStepPageTitle()
    {
        return 'Add a certificate';
    }

    public function getBackLinkText()
    {
        return 'Back to add a certificate';
    }

    public function hasConfirmationPage()
    {
        return $this->twoFaFeatureToggle->isEnabled();
    }

    private function shouldSeeOrderSecurityCard()
    {
        $personId = $this->context->getLoggedInPersonId();
        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($personId);
        $personalDetails = new PersonalDetails($personalDetailsData);

        try {
            if ($this->authorisationService->getSecurityCardForUser($personalDetails->getUsername())) {
                return false;
            }
        } catch (ResourceNotFoundException $exception) {
            $testerAuthorisation = $this->personProfileGuardBuilder->getTesterAuthorisation($personId);
            $securityCardOrders = $this->authorisationService->getSecurityCardOrders($personalDetails->getUsername());

            if ($securityCardOrders->getCount() === 0 &&
                ($testerAuthorisation->getGroupAStatus()->getCode() == AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED ||
                    $testerAuthorisation->getGroupBStatus()->getCode() == AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED)
            ) {
                return true;
            }
        }

        return false;
    }

    public function getEditPageLede()
    {
        return null;
    }
}