<?php
namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;

class QualificationDetailsEditProcess extends QualificationDetailsAbstractProcess
{
    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        if($this->context->getTargetPersonId() && $this->context->getGroup()) {
            try {
                $qualificationDetails = $this->qualificationDetailsMapper->getQualificationDetails(
                    $this->context->getTargetPersonId(), $this->context->getGroup()
                );

                return QualificationDetailsMapper::mapDtoToFormData($qualificationDetails);
            } catch(NotFoundException $e) {

            }
        }
        return [];
    }

    /**
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToEditPage()
    {
        $route = $this->qualificationDetailsRoutes->getEditRoute();
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
        $route = $this->qualificationDetailsRoutes->getEditReviewRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
                self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
                self::ROUTE_PARAM_FORM_UUID => $formUuid,
            ];
        return new RedirectToRoute($route, $params);
    }

    protected function getReviewStepBackUrl($formUuid)
    {
        $route = $this->qualificationDetailsRoutes->getEditRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
                self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
            ];
        return $this->context->getController()->url()->fromRoute($route, $params).'?formUuid='.$formUuid;
    }

    protected function getBreadcrumbCurrentStepName()
    {
        return 'Change certificate details';
    }

    public function getSuccessfulEditMessage()
    {
        return 'Group '.strtoupper($this->context->getGroup()).' certificate changed successfully.';
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     */
    public function update($formData)
    {
        $dto = QualificationDetailsMapper::mapFormDataToDto($formData, $this->context->getGroup());

        return $this->qualificationDetailsMapper->updateQualificationDetails($this->context->getTargetPersonId(),
            $this->context->getGroup(), $dto);
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     */
    public function isAuthorised(
        MotAuthorisationServiceInterface $authorisationService)
    {
        $personId = $this->context->getTargetPersonId();

        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($personId);
        $personalDetails = new PersonalDetails($personalDetailsData);
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            ContextProvider::YOUR_PROFILE_CONTEXT
        );

        return $personProfileGuard->canUpdateQualificationDetails(strtoupper($this->context->getGroup()));
    }

    public function getEditStepPageTitle()
    {
        return 'Change a certificate';
    }

    public function getBackLinkText()
    {
        return 'Back to change a certificate';
    }

    public function getEditPageLede()
    {
        return null;
    }

    public function populateConfirmationPageVariables()
    {

    }
}