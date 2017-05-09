<?php

namespace Organisation\ViewModel\View\Index;

use Core\Formatting\AddressFormatter;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaClient\Entity\Person;
use DvsaClient\Entity\VehicleTestingStation;
use DvsaCommon\Dto\AreaOffice\AreaOfficeDto;
use DvsaCommon\Dto\AuthorisedExaminerPrincipal\AuthorisedExaminerPrincipalDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Organisation\Authorisation\AuthorisedExaminerViewAuthorisation;
use Organisation\Presenter\AuthorisedExaminerPresenter;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Mvc\Controller\Plugin\Url;
use Core\Routing\AeRoutes;

/**
 * Class IndexViewModel.
 */
class IndexViewModel
{
    private $viewAuthorisation;

    /**
     * @var EmployeeViewModel[]
     */
    private $employees = [];
    private $organisation;
    private $vehicleTestingStations = [];
    private $principals = [];

    private $numberOfEmployees = 0;
    private $numberOfVehicleTestingStation = 0;
    private $numberOfPrincipals = 0;

    /**
     * @var AuthorisedExaminerPresenter
     */
    private $presenter;

    /**
     * @var EmployeeViewModel
     */
    private $lastEmployee;

    /**
     * @var Person
     */
    private $lastPrincipal;

    /**
     * @var VehicleTestingStationDto
     */
    private $lastVts;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @param AuthorisedExaminerViewAuthorisation $viewAuthorisation
     * @param OrganisationDto                     $organisation
     * @param AuthorisedExaminerPresenter[]       $presenter
     * @param VehicleTestingStationDto[]          $vehicleTestingStations
     * @param OrganisationPositionDto[]           $positions
     * @param AuthorisedExaminerPrincipalDto[]    $principals
     * @param Url                                 $urlHelper
     */
    public function __construct(
        AuthorisedExaminerViewAuthorisation $viewAuthorisation,
        OrganisationDto $organisation,
        AuthorisedExaminerPresenter $presenter,
        $vehicleTestingStations,
        $positions,
        $principals,
        Url $urlHelper
    ) {
        $this->viewAuthorisation = $viewAuthorisation;

        $this->organisation = $organisation;
        $this->vehicleTestingStations = $vehicleTestingStations;
        $this->presenter = $presenter;
        $this->employees = $this->groupPositionsByPerson($positions);
        $this->principals = $principals;
        $this->urlHelper = $urlHelper;

        $this->numberOfEmployees = count($this->employees);
        $this->numberOfVehicleTestingStation = count($this->vehicleTestingStations);
        $this->numberOfPrincipals = count($this->principals);

        $this->lastEmployee = end($this->employees);
        $this->lastVts = end($vehicleTestingStations);
        $this->lastPrincipal = end($this->principals);
    }

    public function getViewAuthorisation()
    {
        return $this->viewAuthorisation;
    }

    public function getVehicleTestingStations()
    {
        return $this->vehicleTestingStations;
    }

    public function getPrincipals()
    {
        return $this->principals;
    }

    /**
     * @return AuthorisedExaminerPresenter
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @param int $principalIndex
     *
     * @return ContactDto
     */
    public function getPrincipalContactDetails($principalIndex)
    {
        return $this->principals[$principalIndex]->getContactDetails();
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return int
     */
    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    /**
     * @param EmployeeViewModel $employee
     *
     * @return bool
     */
    public function isLastEmployee(EmployeeViewModel $employee)
    {
        return $this->lastEmployee === $employee;
    }

    /**
     * @param PersonDto $person
     *
     * @return bool
     */
    public function isLastPrincipal(PersonDto $person)
    {
        return $this->lastPrincipal === $person;
    }

    /**
     * @return int
     */
    public function getNumberOfVehicleTestingStations()
    {
        return $this->numberOfVehicleTestingStation;
    }

    /**
     * @param VehicleTestingStation $vts
     *
     * @return bool
     */
    public function isLastVts(VehicleTestingStation $vts)
    {
        return $this->lastVts === $vts;
    }

    /**
     * @return \Organisation\ViewModel\View\Index\EmployeeViewModel[]
     */
    public function getEmployees()
    {
        return $this->employees;
    }

    public function shouldViewContactDetailsForVts()
    {
        return $this->getNumberOfVehicleTestingStations() > 0;
    }

    /**
     * @param OrganisationPositionDto[] $positions
     *
     * @return EmployeeViewModel[]
     */
    private function groupPositionsByPerson($positions)
    {
        $employees = [];

        if (!empty($positions)) {
            foreach ($positions as $position) {
                $person = $position->getPerson();

                if (array_key_exists($person->getId(), $employees)) {
                    $employee = $employees[$person->getId()];
                } else {
                    $employee = new EmployeeViewModel($person);
                }

                $employee->addPosition($position);
                $employees[$person->getId()] = $employee;
            }
        }

        return $employees;
    }

    public function hasPrincipals()
    {
        return $this->numberOfPrincipals > 0;
    }

    public function canViewAepSection()
    {
        if (($this->hasPrincipals() && $this->viewAuthorisation->canViewAuthorisedExaminerPrincipals())
            || $this->viewAuthorisation->canCreateAuthorisedExaminerPrincipal()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return AreaOfficeDto
     */
    public function getAssignedAreaOffice()
    {
        return $this->getOrganisation()->getAuthorisedExaminerAuthorisation()->getAssignedAreaOffice();
    }

    /**
     * @return AreaOfficeDto
     */
    public function getStatusName()
    {
        return $this->getOrganisation()->getAuthorisedExaminerAuthorisation()->getStatus()->getName();
    }

    /**
     * @return Url
     */
    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    public function buildAEBusinessDetailsSummaryTable()
    {
        $permissions = $this->getViewAuthorisation();
        $organisation = $this->getOrganisation();
        $presenter = $this->getPresenter();
        $orgId = $organisation->getId();
        $urlHelper = $this->getUrlHelper();
        $withdrawalDate = '';
        $assignedAreaOffice = $this->getAssignedAreaOffice();
        $aoNumber = $assignedAreaOffice ? $assignedAreaOffice->getSiteNumber() : 'N/A';

        $table = new GdsTable();
        $row = $table->newRow('ae-name')->setLabel('Name')->setValue($presenter->getName() ?: 'N/A');
        if ($permissions->canUpdateAEBusinessDetailsName()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_NAME_PROPERTY), 'Change Name');
        }
        $row = $table->newRow('ae-trading-name')->setLabel('Trading name')->setValue($presenter->getTradingName() ?: 'N/A');
        if ($permissions->canUpdateAEBusinessDetailsTradingName()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_TRADING_NAME_PROPERTY), 'Change Trading Name');
        }
        $row = $table->newRow('ae-type')->setLabel('Business type')->setValue($presenter->getCompanyType() ?: 'N/A');
        if ($presenter->isBusinessTypeCompany()) {
            $row->setValueMetaData($presenter->getCompanyNumber() ?: 'N/A');
        }
        if ($permissions->canUpdateAEBusinessDetailsBusinessType()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_BUSINESS_TYPE_PROPERTY), 'Change Business Type');
        }
        $table->newRow('ae-number')->setLabel('Authorised Examiner ID')->setValue($presenter->getNumber() ?: 'N/A');
        if ($permissions->canViewAeStatus()) {
            $table->newRow('ae-appeal-status')->setLabel('Appeal status')->setValue('N/A');
            $table->newRow('ae-withdraw-date')->setLabel('Withdrawal date')->setValue($withdrawalDate ? $withdrawalDate : 'N/A');
            $row = $table->newRow('ae-dvsa-area-office')->setLabel('DVSA Area Office')->setValue($aoNumber);
            if ($permissions->canUpdateAEBusinessDetailsDVSAAreaOffice()) {
                $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_DVSA_AREA_OFFICE_STATUS_PROPERTY), 'Change DVSA AO');
            }
        }

        if ($permissions->canUpdateAEBusinessDetailsStatus()) {
            $row = $table->newRow('ae-auth-status')->setLabel('Status')->setValue($this->getStatusName() ?: 'N/A');
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_STATUS_PROPERTY), 'Change Status');
        }

        return $table;
    }

    public function buildAERegisteredOfficeSummaryTable()
    {
        $permissions = $this->getViewAuthorisation();
        $urlHelper = $this->getUrlHelper();
        $organisation = $this->getOrganisation();
        $orgId = $organisation->getId();
        $contactDetail = $organisation->getRegisteredCompanyContactDetail();
        $emailAddress = $contactDetail->getPrimaryEmailAddress();
        $email = $emailAddress ? '<a href=mailto:'.$emailAddress.'>'.$emailAddress.'</a>' : 'N/A';
        $address = $contactDetail->getAddress();
        $addressString = null;
        if ($address) {
            $addressString = (new AddressFormatter())->escapedDtoToMultiLine($address, true);
            $addressString = $addressString ?: 'N/A';
        }

        $table = new GdsTable();
        $table->setHeader('Registered office');
        $row = $table->newRow('reg-AE-address')->setLabel('Address')->setValue($addressString, false);
        if ($permissions->canUpdateAEContactDetailsRegisteredOfficeAddress()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_REGISTERED_ADDRESS_PROPERTY), 'Change Registered Address');
        }
        $row = $table->newRow('reg-email')->setLabel('Email')->setValue($email, false);
        if ($permissions->canUpdateAEContactDetailsRegisteredOfficeEmail()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_REGISTERED_EMAIL_PROPERTY), 'Change Registered Email');
        }
        $row = $table->newRow('reg-telephone')->setLabel('Telephone')->setValue($contactDetail->getPrimaryPhoneNumber());
        if ($permissions->canUpdateAEContactDetailsRegisteredOfficeTelephone()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_REGISTERED_TELEPHONE_PROPERTY), 'Change Registered Telephone');
        }

        return $table;
    }

    public function buildAECorrespondenceSummaryTable()
    {
        $permissions = $this->getViewAuthorisation();
        $urlHelper = $this->getUrlHelper();
        $organisation = $this->getOrganisation();
        $orgId = $organisation->getId();
        $correspondenceDetail = $organisation->getCorrespondenceContactDetail();
        $emailAddress = $correspondenceDetail->getPrimaryEmailAddress();
        $email = $emailAddress ? '<a href=mailto:'.$emailAddress.'>'.$emailAddress.'</a>' : 'N/A';
        $address = $correspondenceDetail->getAddress();
        $addressString = null;
        if ($address) {
            $addressString = (new AddressFormatter())->escapedDtoToMultiLine($address, true);
            $addressString = $addressString ?: 'N/A';
        }

        $table = new GdsTable();
        $table->setHeader('Correspondence');
        $row = $table->newRow('cor-address')->setLabel('Address')->setValue($addressString, false);
        if ($permissions->canUpdateAEContactDetailsCorrespondenceAddress()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_CORRESPONDENCE_ADDRESS_PROPERTY), 'Change Registered Address');
        }
        $row = $table->newRow('cor-email')->setLabel('Email')->setValue($email, false);
        if ($permissions->canUpdateAEContactDetailsCorrespondenceEmail()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_CORRESPONDENCE_EMAIL_PROPERTY), 'Change Registered Email');
        }
        $row = $table->newRow('cor-phone')->setLabel('Telephone')->setValue($correspondenceDetail->getPrimaryPhoneNumber());
        if ($permissions->canUpdateAEContactDetailsCorrespondenceTelephone()) {
            $row->addActionLink('Change', AeRoutes::of($urlHelper)->aeEditProperty($orgId, UpdateAePropertyAction::AE_CORRESPONDENCE_TELEPHONE_PROPERTY), 'Change Registered Telephone');
        }

        return $table;
    }
}
