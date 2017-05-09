<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Utility\Hydrator;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Mapper\AddressMapper;

/**
 * Class ContactDetailMapper.
 */
class ContactMapper
{
    private $hydrator;

    private $requiredFieldsForContact
        = ['faxNumber'];

    private $requiredFieldsForPhone
        = [
            'id',
            'number',
            'isPrimary',
        ];

    private $requiredFieldsForEmail
        = [
            'id',
            'email',
            'isPrimary',
        ];

    private $requiredFieldsForAddress
        = [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'postcode',
            'town',
        ];

    public function __construct()
    {
        $this->hydrator = new Hydrator();
        $this->addressMapper = new AddressMapper();
    }

    /**
     * @param $contacts
     *
     * @return array
     */
    public function manyToArray($contacts)
    {
        $data = [];

        foreach ($contacts as $contact) {
            $data[] = $this->toArray($contact);
        }

        return $data;
    }

    /**
     * @param ContactDetail $contact
     *
     * @return array
     */
    public function toArray(ContactDetail $contact)
    {
        $contactData = $this->hydrator->extract(
            $contact,
            $this->requiredFieldsForContact
        );
        $contactData['_clazz'] = 'ContactDetails';

        if (null != $contact->getPhones()) {
            $phonesData = [];
            foreach ($contact->getPhones() as $phone) {
                $phoneData = $this->hydrator->extract($phone, $this->requiredFieldsForPhone);
                $phoneData['contactType'] = $phone->getContactType()->getCode();
                $phoneData['_clazz'] = 'Phone';
                $phonesData[] = $phoneData;
            }
            $contactData['phones'] = $phonesData;
        }

        if (null != $contact->getEmails()) {
            $emailsData = [];
            foreach ($contact->getEmails() as $email) {
                $emailData = $this->hydrator->extract($email, $this->requiredFieldsForEmail);
                $emailData['_clazz'] = 'Email';
                $emailsData[] = $emailData;
            }
            $contactData['emails'] = $emailsData;
        }

        $addressData = [
            '_clazz' => 'Address',
        ];

        if ($contact->getAddress()) {
            $addressData += $this->hydrator->extract(
                $contact->getAddress(),
                $this->requiredFieldsForAddress
            );
        }

        $contactData['address'] = $addressData;

        return $contactData;
    }

    public function toDto(ContactDetail $contact, ContactDto $contactDto = null)
    {
        $contactDto = $contactDto ? $contactDto : new OrganisationContactDto();

        if ($contact->getPhones()) {
            $phones = $contact->getPhones();

            $phones = $contact->getPhones()->toArray();
            usort($phones, function ($a, $b) {
                return $a->getCreatedOn() < $b->getCreatedOn();
            });

            $phoneDtos = [];
            foreach ($phones as $phone) {
                $phoneDto = new PhoneDto();
                $phoneDto->setId($phone->getId());
                $phoneDto->setNumber($phone->getNumber());
                $phoneDto->setIsPrimary($phone->getIsPrimary());
                $phoneDto->setContactType($phone->getContactType()->getCode());

                $phoneDtos[] = $phoneDto;
            }

            $contactDto->setPhones($phoneDtos);
        }

        if ($contact->getEmails()) {
            $emails = $contact->getEmails();

            $emails = $contact->getEmails()->toArray();
            usort($emails, function ($a, $b) {
                return $a->getCreatedOn() < $b->getCreatedOn();
            });

            $emailDtos = [];
            foreach ($emails as $email) {
                $emailDto = new EmailDto();
                $emailDto->setId($email->getId());
                $emailDto->setEmail($email->getEmail());
                $emailDto->setIsPrimary($email->getIsPrimary());

                $emailDtos[] = $emailDto;
            }

            $contactDto->setEmails($emailDtos);
        }

        $address = $contact->getAddress();
        $contactDto->setAddress($this->addressMapper->toDto($address));

        return $contactDto;
    }
}
