<?php

namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\MotTestingAnnualCertificateApiResource;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesRemoveViewModel;
use Zend\Form\Form;

class AnnualAssessmentCertificatesRemoveProcess implements SingleStepProcessInterface, AutoWireableInterface
{
    /** @var AnnualAssessmentCertificatesFormContext */
    private $context;
    private $contextProvider;
    private $routes;
    private $apiResource;
    private $breadcrumbs;
    private $permissions;
    /** @var MotTestingAnnualCertificateDto */
    private $dto;

    public function __construct(
        ContextProvider $contextProvider,
        AnnualAssessmentCertificatesRoutes $routes,
        MotTestingAnnualCertificateApiResource $apiResource,
        CertificatesBreadcrumbs $breadcrumbs,
        AnnualAssessmentCertificatesPermissions $permissions
    ) {
        $this->contextProvider = $contextProvider;
        $this->routes = $routes;
        $this->apiResource = $apiResource;
        $this->breadcrumbs = $breadcrumbs;
        $this->permissions = $permissions;
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, AnnualAssessmentCertificatesFormContext::class);
        $this->context = $context;
    }

    /**
     * Will make a call to API to update the data from the form.
     *
     * @param $formData
     *
     * @return
     */
    public function update($formData)
    {
        $this->apiResource->remove(
            $this->context->getTargetPersonId(),
            $this->context->getGroup(),
            $this->context->getCertificateId()
        );
    }

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values).
     *
     * @return array
     */
    public function getPrePopulatedData()
    {
        $this->dto = $this->apiResource->get(
            $this->context->getTargetPersonId(),
            $this->context->getGroup(),
            $this->context->getCertificateId()
        );

        return [];
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        return 'Remove certificate';
    }

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     *
     * @return array
     */
    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        return $this->breadcrumbs->getBreadcrumbsForAnnualAssessmentCertificate(
            $this->context->getTargetPersonId(),
            $this->context->getController(),
            $this->getEditStepPageTitle()
        );
    }

    /**
     * Zend form used to edit values.
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        return new Form();
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted.
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        return sprintf('Group %s annual assessment certificate removed successfully.', $this->context->getGroup());
    }

    /**
     * The title that will be displayed on the form page.
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        return 'Remove your assessment certificate';
    }

    /**
     * The sub title that will be displayed on the edit and review pages.
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        return 'Your profile';
    }

    /**
     * @param Form $form
     *
     * @return AnnualAssessmentCertificatesRemoveViewModel
     */
    public function buildEditStepViewModel($form)
    {
        $table = new GdsTable();
        $table->newRow()->setLabel('Certificate number')->setValue(
            $this->dto->getCertificateNumber()
        );
        $table->newRow()->setLabel('Date awarded')->setValue(
            $this->dto->getExamDate()->format(DateTimeDisplayFormat::FORMAT_DATE)
        );
        $table->newRow()->setLabel('Score achieved')->setValue(
            $this->dto->getScore().'%'
        );

        $params = $this->context->getController()->params()->fromRoute() +
            [
                'id' => $this->context->getTargetPersonId(),
                'group' => $this->context->getGroup(),
            ];

        return new AnnualAssessmentCertificatesRemoveViewModel(
            $table,
            $this->getEditStepPageTitle(),
            $this->getPageSubTitle(),
            $this->getSubmitButtonText(),
            $this->routes->getRoute(),
            $params,
            ["query" => $this->getBackToQueryParam()]
        );
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        $route = $this->routes->getRoute();
        $params = $this->context->getController()->params()->fromRoute() +
            [
                'id' => $this->context->getTargetPersonId(),
                'group' => $this->context->getGroup(),
            ];

        return new RedirectToRoute($route, $params, $this->getBackToQueryParam());
    }

    /**
     * Says if the users is authorised to reach the page.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     *
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $this->permissions->isGrantedRemove(
            $this->context->getTargetPersonId(),
            $this->context->getLoggedInPersonId()
        );
    }

    public function getEditPageLede()
    {
        return sprintf('Confirm that you want to remove the Group %s annual assessment certificate',
            $this->context->getGroup());
    }

    private function getBackToQueryParam()
    {
        $backTo = $this->context->getController()->params()->fromQuery("backTo");

        return [
            "backTo" => $backTo
        ];
    }
}
