<?php

namespace DvsaClient\Entity;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Formatting\PersonFullNameFormatter;

/**
 * Class AuthorisedExaminerPrincipal.
 */
class AuthorisedExaminerPreincipal
{
    private $contactDetails;
    private $firstName;
    private $middleName;
    private $familyName;
    private $dateOfBirth;

    /**
     * @param ContactDetail $contactDetails
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setContactDetails(ContactDetail $contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * @return ContactDetail
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * @param string $firstName
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $middleName
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $familyName
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param \DateTime $dateOfBirth
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string format: j F Y
     */
    public function displayDateOfBirth()
    {
        return DateTimeDisplayFormat::textDate($this->dateOfBirth);
    }

    public function getDisplayName()
    {
        return (new PersonFullNameFormatter())
            ->format($this->getFirstName(), $this->getMiddleName(), $this->getFamilyName());
    }
}
