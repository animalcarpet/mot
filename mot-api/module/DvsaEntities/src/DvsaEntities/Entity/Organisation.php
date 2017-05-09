<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Organisation.
 *
 * @ORM\Table(
 *  name="organisation",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 *  indexes={@ORM\Index(name="fk_organisation_type", columns={"organisation_type_id"})}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\OrganisationRepository")
 */
class Organisation extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Organisation';
    const SLOT_BALANCE = 0;
    const SLOT_WARNING = 15;

    /**
     * @var string
     *
     * @ORM\Column(name="registered_company_number", type="string", length=45, nullable=true)
     */
    private $registeredCompanyNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="trading_name", type="string", length=60, nullable=true)
     */
    private $tradingAs;

    /**
     * @var OrganisationType
     *
     * @ORM\ManyToOne(targetEntity="OrganisationType")
     * @ORM\JoinColumn(name="organisation_type_id", referencedColumnName="id")
     **/
    private $organisationType;

    /**
     * @var CompanyType
     *
     * @ORM\ManyToOne(targetEntity="CompanyType")
     * @ORM\JoinColumn(name="company_type_id", referencedColumnName="id")
     **/
    private $companyType;

    /**
     * @var OrganisationContact[]
     *
     * @ORM\OneToMany(
     *      targetEntity="\DvsaEntities\Entity\OrganisationContact",
     *      mappedBy="organisation",
     *      cascade={"persist"},
     *      orphanRemoval=true
     * )
     */
    private $contacts;

    /**
     * @var AuthorisationForAuthorisedExaminer
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\AuthorisationForAuthorisedExaminer", mappedBy="organisation")
     */
    private $authorisedExaminer;

    /**
     * @var OrganisationBusinessRoleMap[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\OrganisationBusinessRoleMap", mappedBy="organisation")
     */
    private $positions;

    /**
     * @var Site[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\Site", mappedBy="organisation")
     */
    private $sites;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="slots_balance", nullable=false)
     */
    private $slotBalance = self::SLOT_BALANCE;

    /**
     * @var int
     *
     * @ORM\Column(name="slots_warning", type="integer", length=11, nullable=false)
     */
    private $slotsWarning = self::SLOT_WARNING;

    /**
     * @var int
     *
     * @ORM\Column(name="data_may_be_disclosed", type="integer", length=4, nullable=false)
     */
    private $dataMayBeDisclosed = 0;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->sites = new ArrayCollection();
    }

    /**
     * @param string $typeCode
     *
     * @return OrganisationContact|null
     */
    public function getContactByType($typeCode)
    {
        return ArrayUtils::firstOrNull(
            $this->getContacts(),
            function (OrganisationContact $contact) use ($typeCode) {
                return $contact->getType()->getCode() === $typeCode;
            }
        );
    }

    public function clearCorrespondenceContact()
    {
        $this->findAndRemoveContactByType(OrganisationContactTypeCode::CORRESPONDENCE);
    }

    public function getCorrespondenceContact()
    {
        return $this->getContactByType(OrganisationContactTypeCode::CORRESPONDENCE);
    }

    public function getBusinessContact()
    {
        return $this->getContactByType(OrganisationContactTypeCode::REGISTERED_COMPANY);
    }

    /**
     * @param ContactDetail           $contactDetails
     * @param OrganisationContactType $type
     *
     * @return $this
     */
    public function setContact(ContactDetail $contactDetails, OrganisationContactType $type)
    {
        $this->findAndRemoveContactByType($type->getCode());

        $organisationContact = new OrganisationContact($contactDetails, $type);
        $organisationContact->setOrganisation($this);

        $this->contacts->add($organisationContact);

        return $this;
    }

    /**
     * @param string $contactTypeCode
     */
    private function findAndRemoveContactByType($contactTypeCode)
    {
        /** @var SiteContact $oldContact */
        $oldContact = $this->getContactByType($contactTypeCode);

        if ($oldContact) {
            $this->contacts->removeElement($oldContact);
        }
    }

    /**
     * Set registeredCompanyNumber.
     *
     * @param string $registeredCompanyNumber
     *
     * @return Organisation
     */
    public function setRegisteredCompanyNumber($registeredCompanyNumber)
    {
        $this->registeredCompanyNumber = $registeredCompanyNumber;

        return $this;
    }

    /**
     * Get registeredCompanyNumber.
     *
     * @return string
     */
    public function getRegisteredCompanyNumber()
    {
        return $this->registeredCompanyNumber;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Organisation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tradingAs.
     *
     * @param string $tradingAs
     *
     * @return Organisation
     */
    public function setTradingAs($tradingAs)
    {
        $this->tradingAs = $tradingAs;

        return $this;
    }

    /**
     * Get tradingAs.
     *
     * @return string
     */
    public function getTradingAs()
    {
        return $this->tradingAs;
    }

    /**
     * Set organisationType.
     *
     * @param OrganisationType $organisationType
     *
     * @return Organisation
     */
    public function setOrganisationType(OrganisationType $organisationType = null)
    {
        $this->organisationType = $organisationType;

        return $this;
    }

    /**
     * Get organisationType.
     *
     * @return OrganisationType
     */
    public function getOrganisationType()
    {
        return $this->organisationType;
    }

    /**
     * Set companyType.
     *
     * @param CompanyType $companyType
     *
     * @return Organisation
     */
    public function setCompanyType(CompanyType $companyType = null)
    {
        $this->companyType = $companyType;

        return $this;
    }

    /**
     * @return CompanyType
     */
    public function getCompanyType()
    {
        return $this->companyType;
    }

    /**
     * @param AuthorisationForAuthorisedExaminer $authorisedExaminer
     *
     * @return Organisation
     */
    public function setAuthorisedExaminer(AuthorisationForAuthorisedExaminer $authorisedExaminer)
    {
        $this->authorisedExaminer = $authorisedExaminer;

        return $this;
    }

    /**
     * @return OrganisationContact[]
     */
    public function getContacts()
    {
        return $this->contacts->getIterator();
    }

    /**
     * @return AuthorisationForAuthorisedExaminer
     */
    public function getAuthorisedExaminer()
    {
        return $this->authorisedExaminer;
    }

    public function isAuthorisedExaminer()
    {
        return $this->getAuthorisedExaminer() !== null;
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function addPosition(OrganisationBusinessRoleMap $position)
    {
        $this->positions->add($position);
    }

    public function addContact(OrganisationContact $contact)
    {
        $contact->setOrganisation($this);

        $this->contacts->add($contact);
    }

    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @return Person|null
     */
    public function getDesignatedManager()
    {
        /** @var OrganisationBusinessRoleMap $aedmPosition */
        $aedmPosition = ArrayUtils::firstOrNull(
            $this->positions,
            function (OrganisationBusinessRoleMap $position) {
                return $position->getOrganisationBusinessRole()->getName()
                === OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER
                && $position->getBusinessRoleStatus()->getCode() === BusinessRoleStatusCode::ACTIVE;
            }
        );

        return $aedmPosition ? $aedmPosition->getPerson() : null;
    }

    /**
     * @param int $slotBalance
     *
     * @return Organisation
     */
    public function setSlotBalance($slotBalance)
    {
        $this->slotBalance = $slotBalance;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlotBalance()
    {
        return $this->slotBalance;
    }

    /**
     * Get slotsWarning.
     *
     * @return int
     */
    public function getSlotsWarning()
    {
        return $this->slotsWarning;
    }

    /**
     * Set slotsWarning.
     *
     * @param int $slotsWarning
     *
     * @return Organisation
     */
    public function setSlotsWarning($slotsWarning)
    {
        $this->slotsWarning = $slotsWarning;

        return $this;
    }

    /**
     * Get dataMayBeDisclosed.
     *
     * @return int
     */
    public function getDataMayBeDisclosed()
    {
        return $this->dataMayBeDisclosed;
    }

    /**
     * Set dataMayBeDisclosed.
     *
     * @param int $dataMayBeDisclosed
     *
     * @return $this
     */
    public function setDataMayBeDisclosed($dataMayBeDisclosed)
    {
        $this->dataMayBeDisclosed = $dataMayBeDisclosed;

        return $this;
    }
}
