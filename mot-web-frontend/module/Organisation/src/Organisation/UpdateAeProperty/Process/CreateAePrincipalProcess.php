<?php
namespace Organisation\UpdateAeProperty\Process;

use Core\Formatting\AddressFormatter;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine2Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine3Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\CountryInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\DateOfBirthInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\MiddleNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\PostcodeInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use Organisation\UpdateAeProperty\AbstractTwoStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\AepForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\View\Helper\Url;

class CreateAePrincipalProcess extends AbstractTwoStepAeProcess implements AutoWireableInterface
{
    private $propertyName = UpdateAePropertyAction::AE_CREATE_AEP_PROPERTY;
    private $permission = PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_CREATE;
    private $submitButtonText = "Continue";
    private $successfulEditMessage = "%s has been added as a Authorised Examiner Principal.";
    private $formPageTitle = "Add a principal";
    private $formPartial = "organisation/update-ae-property/partials/add-principle";
    private $personMapper;
    protected $reviewPageTitle = "Review new Principal";
    protected $reviewPageLede = "";
    protected $reviewPageButtonText = "Add Principal";
    private $aepName = "";

    public function __construct(MapperFactory $mapperFactory, OrganisationMapper $organisationMapper, Url $urlHelper)
    {
        parent::__construct($organisationMapper, $urlHelper);
        $this->personMapper = $mapperFactory->Person;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getFormPartial()
    {
        return $this->formPartial;
    }

    public function createEmptyForm()
    {
        return new AepForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        return [];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $dob = new \DateTime();
        $dob->setDate((int)$formData[AepForm::FIELD_DOB_YEAR], (int)$formData[AepForm::FIELD_DOB_MONTH], (int)$formData[AepForm::FIELD_DOB_DAY]);

        $formData[DateOfBirthInput::FIELD] = $dob->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY);

        unset($formData[AepForm::FIELD_DOB_DAY]);
        unset($formData[AepForm::FIELD_DOB_MONTH]);
        unset($formData[AepForm::FIELD_DOB_YEAR]);

        $this->personMapper->createPrincipalsForOrganisation($this->context->getAeId(), $formData);
    }

    public function getSuccessfulEditMessage()
    {
        return sprintf($this->successfulEditMessage, $this->getAepName());
    }

    public function getEditStepPageTitle()
    {
        return $this->formPageTitle;
    }

    public function transformFormIntoGdsTable(array $formData)
    {
        $table = new GdsTable();

        $name = $this->getAepName($formData);

        $dob = new \DateTime();
        $dob->setDate((int)$formData[AepForm::FIELD_DOB_YEAR], (int)$formData[AepForm::FIELD_DOB_MONTH], (int)$formData[AepForm::FIELD_DOB_DAY]);

        $table->newRow("name")->setLabel("Name")->setValue($name);
        $table->newRow("dob")->setLabel("Date of birth")->setValue($dob->format(DateTimeDisplayFormat::FORMAT_DATE));
        $table->newRow("address")->setLabel("Address")
            ->setValue((new AddressFormatter())->escapeAddressToMultiLine(
                $formData[AddressLine1Input::FIELD],
                $formData[AddressLine2Input::FIELD],
                $formData[AddressLine3Input::FIELD],
                null,
                $formData[TownInput::FIELD],
                $formData[CountryInput::FIELD],
                $formData[PostcodeInput::FIELD]
            )
                , false);

        return $table;
    }

    public function getReviewPageTitle()
    {
        return $this->reviewPageTitle;
    }

    public function getReviewPageLede()
    {
        return $this->reviewPageLede;
    }

    public function getReviewPageButtonText()
    {
        return $this->reviewPageButtonText;
    }

    /**
     * @param array $formData
     * @return string
     */
    private function getAepName(array $formData = [])
    {
        if ($formData) {
            $nameData = [$formData[FirstNameInput::FIELD], $formData[MiddleNameInput::FIELD], $formData[FamilyNameInput::FIELD]];
            $nameData = array_filter($nameData);
            $this->aepName = implode(" ", $nameData);
        }

        return $this->aepName;
    }

    public function getEditPageLede()
    {
        return null;
    }
}
