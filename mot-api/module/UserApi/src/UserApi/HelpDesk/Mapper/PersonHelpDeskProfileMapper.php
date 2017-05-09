<?php

namespace UserApi\HelpDesk\Mapper;

use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Account\AuthenticationMethodDto;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Mapper\AddressMapper;

/**
 * Class PersonHelpDeskProfileMapper.
 */
class PersonHelpDeskProfileMapper
{
    /**
     * @var AddressMapper
     */
    private $addressMapper;

    public function __construct()
    {
        $this->addressMapper = new AddressMapper();
    }

    /**
     * @param Person $person
     *
     * @return PersonHelpDeskProfileDto
     */
    public function fromPersonEntityToDto(Person $person)
    {
        $drivingLicence = $person->getDrivingLicence();

        $dto = new PersonHelpDeskProfileDto();

        $dto->setTitle($person->getTitle() && $person->getTitle()->getId() ? $person->getTitle()->getName() : '');
        $dto->setUserName($person->getUsername());
        $dto->setFirstName($person->getFirstName());
        $dto->setMiddleName($person->getMiddleName());
        $dto->setLastName($person->getFamilyName());
        $dto->setDateOfBirth(DateTimeApiFormat::date($person->getDateOfBirth()));
        if ($drivingLicence !== null) {
            $dto->setDrivingLicenceNumber($drivingLicence->getLicenceNumber());

            if ($drivingLicence->hasCountry()) {
                $dto->setDrivingLicenceRegion($drivingLicence->getCountry());
                $dto->setDrivingLicenceRegionCode($drivingLicence->getCountryCode());
            } else {
                $dto->setDrivingLicenceRegion('');
                $dto->setDrivingLicenceRegionCode('');
            }
        } else {
            $dto->setDrivingLicenceNumber('');
            $dto->setDrivingLicenceRegion('');
            $dto->setDrivingLicenceRegionCode('');
        }
        $this->mapPersonalAddress($person, $dto);

        return $dto;
    }

    /**
     * @param Person                   $person
     * @param PersonHelpDeskProfileDto $dto
     */
    private function mapPersonalAddress(Person $person, PersonHelpDeskProfileDto $dto)
    {
        foreach ($person->getContacts() as $contact) {
            if ($contact->getType()->getName() === PersonContactType::PERSONAL) {
                // Address is optional
                $address = $contact->getDetails()->getAddress();
                $dto->setAddress($address ? $this->addressMapper->toDto($address) : null);

                // Phone is optional
                $phone = $contact->getDetails()->getPrimaryPhone();
                $dto->setTelephone($phone ? $phone->getNumber() : null);

                $this->mapEmail($contact, $dto);
                break;
            }
        }
    }

    /**
     * @param PersonContact            $contact
     * @param PersonHelpDeskProfileDto $dto
     */
    private function mapEmail(PersonContact $contact, PersonHelpDeskProfileDto $dto)
    {
        $primaryEmail = $contact->getDetails()->getPrimaryEmail();
        $dto->setEmail($primaryEmail instanceof Email ? $primaryEmail->getEmail() : null);
    }

    /**
     * @param AuthenticationMethod     $authenticationMethod
     * @param PersonHelpDeskProfileDto $dto
     */
    public function mapAuthenticationMethod(AuthenticationMethod $authenticationMethod, PersonHelpDeskProfileDto $dto)
    {
        $authenticationMethodDto = new AuthenticationMethodDto();

        $authenticationMethodDto
            ->setName($authenticationMethod->getName())
            ->setCode($authenticationMethod->getCode());

        $dto->setAuthenticationMethod($authenticationMethodDto);
    }
}
