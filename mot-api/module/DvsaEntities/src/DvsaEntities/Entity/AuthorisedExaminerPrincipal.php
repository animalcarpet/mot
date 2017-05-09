<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Aep.
 *
 * @ORM\Table(name="authorised_examiner_principal")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\AuthorisedExaminerPrincipalRepository")
 */
class AuthorisedExaminerPrincipal extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var AuthorisationForAuthorisedExaminer
     *
     * @ORM\ManyToOne(targetEntity="AuthorisationForAuthorisedExaminer")
     * @ORM\JoinColumn(name="auth_for_ae_id", referencedColumnName="id")
     */
    private $authorisationForAuthorisedExaminer;

    /**
     * @var ContactDetail
     *
     * @ORM\OneToOne(targetEntity="ContactDetail", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="contact_detail_id", referencedColumnName="id")
     */
    private $contactDetails;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=45, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=45, nullable=true)
     */
    private $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="family_name", type="string", length=45, nullable=false)
     */
    private $familyName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @param AuthorisationForAuthorisedExaminer $afae
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function setAuthorisationForAuthorisedExaminer(AuthorisationForAuthorisedExaminer $afae)
    {
        $this->authorisationForAuthorisedExaminer = $afae;

        return $this;
    }

    /**
     * @return AuthorisationForAuthorisedExaminer
     */
    public function getAuthorisationForAuthorisedExaminer()
    {
        return $this->authorisationForAuthorisedExaminer;
    }

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

    public function getDisplayName()
    {
        return (new PersonFullNameFormatter())
            ->format($this->getFirstName(), $this->getMiddleName(), $this->getFamilyName());
    }
}
