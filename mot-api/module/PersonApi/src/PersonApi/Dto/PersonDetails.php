<?php

namespace PersonApi\Dto;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Licence;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use Zend\Stdlib\Hydrator;

/**
 * DTO for personal and contact details.
 */
class PersonDetails
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $middleName;

    /**
     * @var string
     */
    private $surname;

    /**
     * @var string
     */
    private $drivingLicenceNumber;

    /**
     * @var string
     */
    private $drivingLicenceRegion;

    /**
     * @var string
     */
    private $dateOfBirth;

    /**
     * @var string
     */
    private $addressLine1;

    /**
     * @var string
     */
    private $addressLine2;

    /**
     * @var string
     */
    private $addressLine3;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var null|string
     */
    private $phone;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @var string
     */
    private $username;

    /**
     * @var \DvsaEntities\Entity\OrganisationBusinessRoleMap[]
     */
    private $positions;

    /**
     * @var array
     */
    private $roles;

    /**
     * @param Person        $person
     * @param ContactDetail $profileContactDetails
     * @param EntityManager $entityManager
     * @param array         $roles
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function __construct(
        Person $person,
        ContactDetail $profileContactDetails,
        EntityManager $entityManager,
        $roles
    ) {
        $this->id = $person->getId();
        $this->title = '';

        if ($person->getTitle() && $person->getTitle()->getId()) {
            $this->title = $person->getTitle()->getName();
        }

        $this->gender = $person->getGender()->getName();
        $this->firstName = $person->getFirstName();
        $this->middleName = $person->getMiddleName();
        $this->surname = $person->getFamilyName();
        $this->username = $person->getUsername();
        $this->dateOfBirth = DateTimeApiFormat::date($person->getDateOfBirth());
        $this->positions = $person->findAllOrganisationPositions();

        // VM-10289: Address is optional
        $this->importAddress($profileContactDetails->getAddress());

        // VM-10289: Phone is optional
        $this->importPhone($entityManager, $profileContactDetails);

        // VM-10289: Email is optional
        $this->importEmail($entityManager, $profileContactDetails);

        $licence = $person->getDrivingLicence();
        if ($licence instanceof Licence) {
            $this->drivingLicenceNumber = $licence->getLicenceNumber();

            if ($licence->hasCountry()) {
                $this->drivingLicenceRegion = $licence->getCountryCode();
            }
        }

        $this->roles = $roles;
    }

    /**
     * @param Address|null $address
     */
    public function importAddress(Address $address = null)
    {
        if ($address) {
            $this->addressLine1 = $address->getAddressLine1();
            $this->addressLine2 = $address->getAddressLine2();
            $this->addressLine3 = $address->getAddressLine3();
            $this->town = $address->getTown();
            $this->postcode = $address->getPostcode();
        } else {
            $this->addressLine1 = null;
            $this->addressLine2 = null;
            $this->addressLine3 = null;
            $this->town = null;
            $this->postcode = null;
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param ContactDetail $profileContactDetails
     */
    public function importPhone(EntityManager $entityManager, ContactDetail $profileContactDetails)
    {
        $phoneContactType = $entityManager
            ->getRepository(PhoneContactType::class)
            ->findOneBy(['code' => PhoneContactTypeCode::PERSONAL]);
        if ($phoneContactType) {
            $phone = $entityManager
                ->getRepository(Phone::class)
                ->findOneBy(
                    [
                        'contact' => $profileContactDetails,
                        'contactType' => $phoneContactType,
                    ]
                );

            $this->phone = ($phone instanceof Phone) ? ($phone->getNumber() ?: null) : null;
            if ($phone instanceof Phone) {
                if (null === $phone->getNumber()) {
                    $this->phone = null;
                } else {
                    $this->phone = $phone->getNumber();
                }
            } else {
                $this->phone = null;
            }
        } else {
            $this->phone = null;
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param ContactDetail $profileContactDetails
     */
    public function importEmail(EntityManager $entityManager, ContactDetail $profileContactDetails)
    {
        $email = $entityManager
            ->getRepository(Email::class)
            ->findOneBy([
                'contact' => $profileContactDetails,
                'isPrimary' => true,
            ]);
        $this->email = ($email instanceof Email) ? ($email->getEmail() ?: null) : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $hydrator = new Hydrator\ClassMethods(false);

        return $hydrator->extract($this);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string
     */
    public function getDrivingLicenceNumber()
    {
        return $this->drivingLicenceNumber;
    }

    /**
     * @return string
     */
    public function getDrivingLicenceRegion()
    {
        return $this->drivingLicenceRegion;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $gender
     *
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }
}
