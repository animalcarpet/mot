<?php
namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\TwoStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;
use DateTime;
use Dvsa\Mot\Frontend\PersonModule\Action\Context\AnnualAssessmentCertificatesActionContext;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Form\AnnualAssessmentCertificatesForm;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesAddEditReviewViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesAddEditViewModel;
use DvsaClient\Mapper\AnnualAssessmentCertificatesMapper;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\MotTestingAnnualCertificateApiResource;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

class AnnualAssessmentCertificatesEditProcess implements TwoStepProcessInterface, AutoWireableInterface
{
    const ROUTE_PARAM_ID = 'id';
    const ROUTE_PARAM_GROUP = 'group';
    const ROUTE_PARAM_CERTIFICATE_ID = 'certificateId';
    const ROUTE_PARAM_FORM_UUID = 'formUuid';

    /** @var AnnualAssessmentCertificatesActionContext */
    private $context;

    /** @var  AnnualAssessmentCertificatesRoutes */
    private $annualAssessmentCertificatesRoutes;

    /** @var CertificatesBreadcrumbs */
    private $certificatesBreadcrumbs;

    /** @var AnnualAssessmentCertificatesMapper */
    private $annualAssessmentCertificatesMapper;

    /** @var ContextProvider */
    private $contextProvider;

    /** @var AnnualAssessmentCertificatesPermissions */
    private $certificatesPermissions;

    private $apiResource;

    public function __construct(
        ContextProvider $contextProvider,
        AnnualAssessmentCertificatesRoutes $annualAssessmentCertificatesRoutes,
        AnnualAssessmentCertificatesMapper $annualAssessmentCertificatesMapper,
        CertificatesBreadcrumbs $certificatesBreadcrumbs,
        AnnualAssessmentCertificatesPermissions $certificatesPermissions,
        MotTestingAnnualCertificateApiResource $apiResource
    )
    {
        $this->contextProvider = $contextProvider;
        $this->annualAssessmentCertificatesRoutes = $annualAssessmentCertificatesRoutes;
        $this->annualAssessmentCertificatesMapper = $annualAssessmentCertificatesMapper;
        $this->certificatesBreadcrumbs = $certificatesBreadcrumbs;
        $this->certificatesPermissions = $certificatesPermissions;
        $this->apiResource = $apiResource;
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, FormContext::class);
        $this->context = $context;
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     * @return mixed
     */
    public function update($formData)
    {
        $dto = $this->mapFormToDto($formData);
        $dto->setId($this->context->getCertificateId());

        return $this->apiResource->update(
            $this->context->getTargetPersonId(),
            $this->context->getGroup(),
            $this->context->getCertificateId(),
            $dto
        );
    }


    private function mapFormToDto(array $formData)
    {
        return $this->annualAssessmentCertificatesMapper->mapFormDataToDto($formData);
    }

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        $dto = $this->apiResource->get(
            $this->context->getTargetPersonId(),
            $this->context->getGroup(),
            $this->context->getCertificateId()
        );

        return [
            AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER => $dto->getCertificateNumber(),
            AnnualAssessmentCertificatesForm::FIELD_SCORE => $dto->getScore(),
            AnnualAssessmentCertificatesForm::FIELD_DATE_DAY => $dto->getExamDate()->format("d"),
            AnnualAssessmentCertificatesForm::FIELD_DATE_MONTH => $dto->getExamDate()->format("m"),
            AnnualAssessmentCertificatesForm::FIELD_DATE_YEAR => $dto->getExamDate()->format("Y"),
        ];
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        return 'Review details';
    }

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return array
     */
    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        return $this->certificatesBreadcrumbs->getBreadcrumbsForAnnualAssessmentCertificate(
            $this->context->getTargetPersonId(),
            $this->context->getController(),
            $this->getEditStepPageTitle()
        );
    }

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        return new AnnualAssessmentCertificatesForm();
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        return sprintf('Group %s annual assessment certificate changed successfully.', $this->context->getGroup());
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        return sprintf('Change your group %s assessment certificate', $this->context->getGroup());
    }

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        return ContextProvider::YOUR_PROFILE_CONTEXT === $this->contextProvider->getContext() ?
            'Your profile' : 'User profile';
    }

    /**
     * @param $form
     * @return Object Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        return new AnnualAssessmentCertificatesAddEditViewModel(
            $form,
            $this->getStartPageUrl(),
            $this->getSubmitButtonText()
        );
    }

    private function getStartPageUrl()
    {
        $route = $this->annualAssessmentCertificatesRoutes->getRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId()
            ];
        return $this->context->getController()->url()->fromRoute($route, $params);
    }

    private function getEditPageUrl()
    {
        $route = $this->annualAssessmentCertificatesRoutes->getEditRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
                self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
                self::ROUTE_PARAM_CERTIFICATE_ID => $this->context->getCertificateId()
            ];

        $formUuid = $this->context->getController()->params()->fromRoute('formUuid');

        if (empty($formUuid)) {
            $formUuid = $this->context->getController()->params()->fromQuery('formUuid');
        }

        return $this->context->getController()->url()->fromRoute($route, $params, ["query" => ["formUuid" => $formUuid]]);
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        $route = $this->annualAssessmentCertificatesRoutes->getRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            ];
        return new RedirectToRoute($route, $params);
    }

    /**
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToEditPage()
    {
        $route = $this->annualAssessmentCertificatesRoutes->getEditRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
                self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
                self::ROUTE_PARAM_CERTIFICATE_ID => $this->context->getCertificateId()
            ];
        return new RedirectToRoute($route, $params);
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     * @throws UnauthorisedException
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $this->certificatesPermissions->isGrantedUpdate(
            $this->context->getTargetPersonId(),
            $this->context->getLoggedInPersonId()
        );
    }

    /**
     * This will take the form and create a GdsTable that will be shown as summary
     * for user to review before completing the form.
     *
     * @param array $formData
     * @return GdsTable
     */
    public function transformFormIntoGdsTable(array $formData)
    {
        $table = new GdsTable();

        $table->newRow()->setLabel('Certificate number')->setValue($formData[AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER]);

        $date = new \DateTime();
        $date->setDate(
            (int)$formData[AnnualAssessmentCertificatesForm::FIELD_DATE_YEAR],
            (int)$formData[AnnualAssessmentCertificatesForm::FIELD_DATE_MONTH],
            (int)$formData[AnnualAssessmentCertificatesForm::FIELD_DATE_DAY]
        );
        $table->newRow()->setLabel('Date awarded')->setValue($date->format(DateTimeDisplayFormat::FORMAT_DATE));

        $score = (int)$formData[AnnualAssessmentCertificatesForm::FIELD_SCORE];
        $table->newRow()->setLabel('Score achieved')->setValue($score . '%');

        return $table;
    }

    /**
     * The title that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageTitle()
    {
        return "Review your assessment certificate";
    }

    /**
     * The page lede that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageLede()
    {
        return "Check that the details below are correct before you save the certificate.";
    }

    /**
     * The text that will be displayed on the review page button text
     *
     * @return string
     */
    public function getReviewPageButtonText()
    {
        return 'Save certificate';
    }

    /**
     * @param $formUuid
     * @param $formData
     * @param GdsTable $table
     * @return Object Anything you want to pass to the view file
     */
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table)
    {
        return new AnnualAssessmentCertificatesAddEditReviewViewModel(
            $formData,
            $table,
            $this->getEditPageUrl(),
            $this->getReviewPageButtonText()
        );
    }

    /**
     * @param $formUuid
     * @return AbstractRedirectActionResult $authorisationService
     */
    public function redirectToReviewPage($formUuid)
    {
        $route = $this->annualAssessmentCertificatesRoutes->getEditReviewRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
                self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
                self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
                self::ROUTE_PARAM_CERTIFICATE_ID => $this->context->getCertificateId(),
                self::ROUTE_PARAM_FORM_UUID => $formUuid,
            ];
        return new RedirectToRoute($route, $params);
    }

    /**
     * A two step form data needs to be saved in session to allow switching between form screens.
     * Data will be stored in the session under the key this method provides.
     *
     * @return string
     */
    public function getSessionStoreKey()
    {
        return 'edit-annual-assessment-certificate-' . $this->context->getTargetPersonId() . '-' . $this->context->getGroup();
    }

    public function getEditPageLede()
    {
        return null;
    }

    /**
     * Does the process have a confirmation page at the end.
     * If true, will redirect to the confirmation page on the process,
     * if false will redirect to start with success message
     *
     * @return bool
     */
    public function hasConfirmationPage()
    {
        return false;
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToConfirmationPage()
    {
        // TODO: Implement redirectToConfirmationPage() method.
    }

    /**
     * @return mixed
     */
    public function populateConfirmationPageVariables()
    {
        // TODO: Implement populateConfirmationPageVariables() method.
    }
}