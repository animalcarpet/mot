<?php
/**
 * Created by PhpStorm.
 * User: szymonf
 * Date: 24.03.2016
 * Time: 11:32
 */

namespace Dvsa\Mot\Frontend\PersonModule\Model;


use Application\Data\ApiPersonalDetails;
use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\TwoStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Form\QualificationDetailsForm;
use Dvsa\Mot\Frontend\PersonModule\InputFilter\QualificationDetailsInputFilter;
use Dvsa\Mot\Frontend\PersonModule\Routes\QualificationDetailsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

abstract class QualificationDetailsAbstractProcess implements TwoStepProcessInterface, AutoWireableInterface
{
    protected $qualificationDetailsMapper;
    protected $siteMapper;
    protected $certificatesBreadcrumbs;
    protected $personalDetailsService;
    protected $personProfileGuardBuilder;
    protected $contextProvider;
    protected $qualificationDetailsRoutes;

    const ROUTE_PARAM_ID = 'id';
    const ROUTE_PARAM_GROUP = 'group';
    const ROUTE_PARAM_FORM_UUID = 'formUuid';

    public function __construct(
        QualificationDetailsMapper $qualificationDetailsMapper,
        SiteMapper $siteMapper,
        CertificatesBreadcrumbs $certificatesBreadcrumbs,
        ApiPersonalDetails $personalDetailsService,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        ContextProvider $contextProvider,
        QualificationDetailsRoutes $qualificationDetailsRoutes
    )
    {
        $this->qualificationDetailsMapper = $qualificationDetailsMapper;
        $this->siteMapper = $siteMapper;
        $this->certificatesBreadcrumbs = $certificatesBreadcrumbs;
        $this->personalDetailsService = $personalDetailsService;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->contextProvider = $contextProvider;
        $this->qualificationDetailsRoutes = $qualificationDetailsRoutes;
    }

    /** @var FormContext */
    protected $context;
    
    abstract protected function getBackLinkText();

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, FormContext::class);
        $this->context = $context;
    }

    public function getSubmitButtonText()
    {
        //implemented in child
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
        return $this->certificatesBreadcrumbs->getBreadcrumbsForQualificationDetails(
            $this->context->getTargetPersonId(),
            $this->context->getController(),
            $this->getBreadcrumbCurrentStepName()
        );
    }

    protected function getBreadcrumbCurrentStepName()
    {
        //implemented in child
    }

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        return new QualificationDetailsForm(
            new QualificationDetailsInputFilter(
                $this->qualificationDetailsMapper,
                $this->context->getTargetPersonId(),
                $this->context->getGroup()
            )
        );
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        //implemented in child
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        //implemented in child
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
        return (new ViewModel())
            ->setVariables([
                'form' => $form,
                'backUrl' => $this->getStartPageUrl()
            ]);
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        $route = $this->qualificationDetailsRoutes->getRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
        ];
        return new RedirectToRoute($route, $params);
    }

    protected function getStartPageUrl()
    {
        $route = $this->qualificationDetailsRoutes->getRoute();
        $params = $this->context->getController()->params()->fromRoute() + [
            self::ROUTE_PARAM_ID => $this->context->getTargetPersonId(),
            self::ROUTE_PARAM_GROUP => $this->context->getGroup(),
        ];
        return $this->context->getController()->url()->fromRoute($route, $params);
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

        $table->newRow()->setLabel('Certificate number')->setValue($formData[QualificationDetailsForm::FIELD_CERT_NUMBER]);

        $date = new \DateTime();
        $date->setDate(
            (int)$formData[QualificationDetailsForm::FIELD_DATE_YEAR],
            (int)$formData[QualificationDetailsForm::FIELD_DATE_MONTH],
            (int)$formData[QualificationDetailsForm::FIELD_DATE_DAY]
        );
        $table->newRow()->setLabel('Date awarded')->setValue($date->format(DateTimeDisplayFormat::FORMAT_DATE));

        return $table;
    }

    /**
     * The title that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageTitle()
    {
        return 'Review certificate and demo test details';
    }

    /**
     * The page lede that will be displayed on the review page
     *
     * @return string
     */
    public function getReviewPageLede()
    {
        return 'Check that the details below are correct';
    }

    /**
     * The text that will be displayed on the review page button text
     *
     * @return string
     */
    public function getReviewPageButtonText()
    {
        return "Review certificate details";
    }

    /**
     * @param $formUuid
     * @param $formData
     * @param GdsTable $table
     * @return Object Anything you want to pass to the view file
     */
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table)
    {
        $site = $this->getSiteByNumber($formData['vts-id']);

        return (new ViewModel())
            ->setVariables([
                'formData' => $formData,
                'table' => $table,
                'site' => $site,
                'isViewingHimself' => $this->isViewingHimself(),
                'backUrlText' => $this->getBackLinkText(),
                'backUrl' => $this->getReviewStepBackUrl($formUuid),
            ]);
    }

    /**
     * A two step form data needs to be saved in session to allow switching between form screens.
     * Data will be stored in the session under the key this method provides.
     *
     * @return string
     */
    public function getSessionStoreKey()
    {
        return 'tester-qualification-details-'.$this->context->getTargetPersonId().'-'.$this->context->getGroup();
    }

    protected function mapFormToDto(array $formData)
    {
        $motTestingCertificateDto = (new MotTestingCertificateDto())
            ->setId(1)
            ->setVehicleClassGroupCode(strtoupper($this->context->getGroup()))
            ->setSiteNumber($formData[QualificationDetailsForm::FIELD_VTS_ID])
            ->setCertificateNumber($formData[QualificationDetailsForm::FIELD_CERT_NUMBER])
            ->setDateOfQualification(sprintf("%d-%d-%d",
                $formData[QualificationDetailsForm::FIELD_DATE_YEAR],
                $formData[QualificationDetailsForm::FIELD_DATE_MONTH],
                $formData[QualificationDetailsForm::FIELD_DATE_DAY]
            ));

        return $motTestingCertificateDto;
    }

    protected function getReviewStepBackUrl($formUuid)
    {
        throw new \Exception('implement in child!');
    }

    /**
     * @param $siteNumber
     * @return SiteDto
     */
    private function getSiteByNumber($siteNumber)
    {
        if(!empty($siteNumber)){
            return $this->siteMapper->getByNumber($siteNumber);
        }

        return null;
    }

    private function isViewingHimself()
    {
        return ContextProvider::YOUR_PROFILE_CONTEXT === $this->contextProvider->getContext();
    }

    public function hasConfirmationPage()
    {
        return false;
    }

    public function redirectToConfirmationPage()
    {
        throw new \Exception('implement in child!');
    }

    public function populateConfirmationPageVariables()
    {

    }
}