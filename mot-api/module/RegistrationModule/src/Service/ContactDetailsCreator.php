<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Constants\PersonContactType as PersonContactTypeConstant;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonContactType;

/**
 * Class ContactDetailsCreator.
 */
class ContactDetailsCreator extends AbstractPersistableService
{
    /**
     * personContactType doesn't have a custom repository.
     *
     * @var EntityRepository
     */
    private $personContactTypeRepository;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var ContactDetail
     */
    private $contactDetail;

    /**
     * @var PersonContact
     */
    private $personContact;

    /**
     * @var array
     */
    private $data;

    /**
     * @param EntityManager    $entityManager
     * @param EntityRepository $personContactTypeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $personContactTypeRepository
    ) {
        parent::__construct($entityManager);
        $this->personContactTypeRepository = $personContactTypeRepository;
    }

    /**
     * Create contact detail and assign it to the given person.
     *
     * @param Person $person
     * @param array  $data
     *
     * @return PersonContact
     */
    public function create(Person $person, $data)
    {
        $this->data = $data;

        $this->email = new Email();
        $this->address = new Address();
        $this->contactDetail = new ContactDetail();

        $this->populateCompulsoryFields();
        $this->populateOptionalFields();
        $this->populateDefaultFields();

        /** @var PersonContactType $personContactType */
        $personContactType = $this->personContactTypeRepository->findOneBy(['name' => PersonContactTypeConstant::PERSONAL]);

        $this->personContact = new PersonContact(
            $this->contactDetail,
            $personContactType,
            $person
        );

        $this->save($this->personContact);

        $person->addContact($this->personContact);

        return $this->personContact;
    }

    /**
     * populated fields which application expect them to be supplied by the request.
     */
    private function populateCompulsoryFields()
    {
        $this->email
            ->setEmail($this->data[$this->getDetailsStepName()][DetailsInputFilter::FIELD_EMAIL]);

        $this->address
            ->setAddressLine1($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_ADDRESS_1])
            ->setTown($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_TOWN_OR_CITY])
            ->setPostcode($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_POSTCODE]);
    }

    /**
     * populated optional fields which might be supplied by the request and if not its safe to ignore them.
     */
    private function populateOptionalFields()
    {
        if (isset($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_ADDRESS_2])) {
            $this->address
                ->setAddressLine2($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_ADDRESS_2]);
        }

        if (isset($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_ADDRESS_3])) {
            $this->address
                ->setAddressLine3($this->data[$this->getAddressStepName()][AddressInputFilter::FIELD_ADDRESS_3]);
        }
    }

    /**
     * populated fields are not supplied by the request but we need to set them.
     */
    private function populateDefaultFields()
    {
        $this->email
            ->setIsPrimary(true);

        $this->contactDetail->addEmail($this->email)
            ->setAddress($this->address);
    }
}
