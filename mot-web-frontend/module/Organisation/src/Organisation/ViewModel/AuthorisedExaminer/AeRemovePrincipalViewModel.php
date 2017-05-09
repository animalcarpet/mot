<?php

namespace Organisation\ViewModel\AuthorisedExaminer;

use Core\Formatting\AddressFormatter;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;

/**
 * Class AeRemovePrincipalViewModel.
 */
class AeRemovePrincipalViewModel extends AeFormViewModel
{
    private $authorisedExaminer;
    private $principalName;
    private $address;
    private $dateOfBirth;

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param $this
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return AddressDto
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrincipalName()
    {
        return $this->principalName;
    }

    /**
     * @param $this
     */
    public function setPrincipalName($principalName)
    {
        $this->principalName = $principalName;

        return $this;
    }

    /**
     * @return OrganisationDto
     */
    public function getAuthorisedExaminer()
    {
        return $this->authorisedExaminer;
    }

    /**
     * @return $this
     */
    public function setAuthorisedExaminer($authorisedExaminer)
    {
        $this->authorisedExaminer = $authorisedExaminer;

        return $this;
    }

    /**
     * @return GdsTable
     */
    public function getGdsTable()
    {
        $address = (new AddressFormatter())->escapedDtoToMultiLine($this->getAddress(), true);

        $table = new GdsTable();
        $table->newRow('AE-name')->setLabel('Authorised Examiner')->setValue($this->getAuthorisedExaminer());
        $table->newRow('AE-principal-name')->setLabel('Name')->setValue($this->getPrincipalName());
        $table->newRow('AE-date-of-birth')->setLabel('Date of birth')->setValue($this->getDateOfBirth());
        $table->newRow('AE-address')->setLabel('Address')->setValue($address, false);

        return $table;
    }
}
