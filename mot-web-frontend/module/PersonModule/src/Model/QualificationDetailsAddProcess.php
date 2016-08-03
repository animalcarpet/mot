<?php
namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;

class QualificationDetailsAddProcess extends QualificationDetailsAbstractProcess
{
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


    public function getEditStepPageTitle()
    {
        return 'Add a certificate';
    }

    public function getBackLinkText()
    {
        return 'Back to add a certificate';
    }

    public function getEditPageLede()
    {
        return null;
    }
}