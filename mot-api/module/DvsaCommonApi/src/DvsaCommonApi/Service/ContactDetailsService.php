<?php

namespace DvsaCommonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ContactDetailsValidator;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Repository\PhoneContactTypeRepository;

/**
 * Service to handle Contact Detail entities.
 */
class ContactDetailsService extends AbstractService
{
    private $addressService;
    private $phoneContactTypeRepository;
    private $validator;

    public function __construct(
        EntityManager $entityManager,
        AddressService $addressService,
        PhoneContactTypeRepository $contactTypeRepository,
        ContactDetailsValidator $validator
    ) {
        parent::__construct($entityManager);

        $this->addressService = $addressService;
        $this->phoneContactTypeRepository = $contactTypeRepository;
        $this->validator = $validator;
    }

    /**
     * @param ContactDto    $contactDto
     * @param ContactDetail $contactDetails
     *
     * @return ContactDetail
     */
    public function setContactDetailsFromDto(ContactDto $contactDto, ContactDetail $contactDetails)
    {
        //  ----  set address ----
        $this->updateAddressInContactDetails($contactDetails, $contactDto);

        //  ----  add/update/remove phones and emails ----
        $this->updatePhonesInContactDetails($contactDetails, $contactDto->getPhones());
        $this->updateEmailsInContactDetails($contactDetails, $contactDto->getEmails());

        $this->entityManager->persist($contactDetails);
        $this->entityManager->flush();

        return $contactDetails;
    }

    /**
     * @param ContactDto    $contactDto
     * @param ContactDetail $contactDetails
     *
     * @return ContactDetail
     */
    public function patchContactDetailsFromDto(ContactDto $contactDto, ContactDetail $contactDetails)
    {
        //  ----  set address ----
        $this->updateAddressInContactDetails($contactDetails, $contactDto);

        //  ----  add/update/remove phones and emails ----
        $this->updatePhonesInContactDetails($contactDetails, $contactDto->getPhones());
        $this->updateEmailsInContactDetails($contactDetails, $contactDto->getEmails());

        $this->entityManager->persist($contactDetails);
        $this->entityManager->flush();

        return $contactDetails;
    }

    /**
     * @param ContactDetail $contactDetails
     * @param ContactDto    $contactDto
     */
    private function updateAddressInContactDetails(ContactDetail $contactDetails, ContactDto $contactDto)
    {
        $addressEntity = $contactDetails->getAddress();
        $addressDto = $contactDto->getAddress();

        //  --  make any modification only if dto provided  --
        if ($addressDto === null) {
            return;
        }

        if ($addressDto instanceof AddressDto && !$addressDto->isEmpty()) {
            if ($addressEntity === null) {
                $addressEntity = new Address();
                $contactDetails->setAddress($addressEntity);
            }

            $isNeedValidate = true;
            if ($contactDto instanceof OrganisationContactDto) {
                $isNeedValidate = ($contactDto->getType() !== OrganisationContactTypeCode::CORRESPONDENCE);
            }

            $this->addressService->persist($addressEntity, $addressDto->toArray(), $isNeedValidate);
        } elseif ($addressEntity instanceof Address) {
            $this->entityManager->remove($addressEntity);
            $contactDetails->setAddress(null);
        }
    }

    private function updatePhonesInContactDetails(ContactDetail $contactDetails, array $phoneDtos)
    {
        /** @var \Doctrine\Common\Collections\ArrayCollection $phoneEntities */
        $phoneEntities = $contactDetails->getPhones();

        /** @var PhoneDto $phoneDto */
        foreach ($phoneDtos as $phoneDto) {
            $id = $phoneDto->getId();
            $type = $phoneDto->getContactType();
            $isPrimary = $phoneDto->isPrimary();
            $number = trim($phoneDto->getNumber());

            //  --  find entity by id or if id is null, then if it is primary --
            $phoneEntity = ArrayUtils::firstOrNull(
                $phoneEntities,
                function (Phone $phone) use ($id, $type, $isPrimary) {
                    return
                        (
                            $id === null
                            && $isPrimary === true
                            && $phone->getContactType()->getCode() === $type
                            && $phone->getIsPrimary() === $isPrimary
                        )
                        || ((int) $phone->getId() === $id);
                }
            );

            if ((string) $number === '') {
                if ($phoneEntity !== null) {
                    //  --  if number is empty, then drop entity in db    --
                    $this->entityManager->remove($phoneEntity);
                    $contactDetails->removePhone($phoneEntity);
                }
            } else {
                //  --  if null, then it mean a new entity --
                if ($phoneEntity === null) {
                    $phoneEntity = new Phone();

                    $contactDetails->addPhone($phoneEntity);
                }

                //  --  set(update) data from dto --
                $phoneEntity
                    ->setContactType($this->phoneContactTypeRepository->getByCode($phoneDto->getContactType()))
                    ->setIsPrimary($phoneDto->isPrimary())
                    ->setNumber($number);
            }
        }
    }

    private function updateEmailsInContactDetails(ContactDetail $contactDetails, array $emailDtos)
    {
        /** @var \Doctrine\Common\Collections\ArrayCollection $emailEntities */
        $emailEntities = $contactDetails->getEmails();

        /** @var EmailDto $emailDto */
        foreach ($emailDtos as $emailDto) {
            $id = $emailDto->getId();
            $isPrimary = $emailDto->isPrimary();

            //  --  find entity by id or if id is null, then if it is primary --
            /** @var Email $emailEntity */
            $emailEntity = ArrayUtils::firstOrNull(
                $emailEntities,
                function (Email $email) use ($id, $isPrimary) {
                    return ((int) $email->getId() === $id)
                    || ($id === null && $isPrimary === true && $email->getIsPrimary() === $isPrimary);
                }
            );

            if (empty($emailDto->getEmail())) {
                if ($emailEntity !== null) {
                    //  --  if email is empty, then drop email in db    --
                    $this->entityManager->remove($emailEntity);
                    $contactDetails->removeEmail($emailEntity);
                }
            } else {
                //  --  if null, then it mean a new entity --
                if ($emailEntity === null) {
                    $emailEntity = new Email();

                    $contactDetails->addEmail($emailEntity);
                }

                //  --  set(update) data from dto --
                $emailEntity
                    ->setIsPrimary($emailDto->isPrimary())
                    ->setEmail(trim($emailDto->getEmail()));
            }
        }
    }

    public function create(array $data, $phoneContactTypeCode, $arePhonesFaxesAndEmailsPrimary = false)
    {
        // WK: $arePhonesFaxesAndEmailsPrimary should be kicked out,
        // If at all we want to store info that phone is primary then it should be kept in $data.

        $this->validator->validate($data);

        $address = $this->addressService->persist(new Address(), $data);

        $contactType = $this->phoneContactTypeRepository->getByCode($phoneContactTypeCode);

        $phone = new Phone();
        $phone
            ->setNumber($data['phoneNumber'])
            ->setIsPrimary($arePhonesFaxesAndEmailsPrimary)
            ->setContactType($contactType);

        $email = new Email();
        $email
            ->setEmail($data['email'])
            ->setIsPrimary($arePhonesFaxesAndEmailsPrimary);

        $contactDetails = new ContactDetail();
        $contactDetails
            ->setAddress($address)
            ->addPhone($phone)
            ->addEmail($email);

        if (!empty($data['faxNumber'])) {
            $faxContactType = $this->phoneContactTypeRepository->getByCode(PhoneContactTypeCode::FAX);

            $fax = new Phone();
            $fax
                ->setNumber($data['faxNumber'])
                ->setIsPrimary($arePhonesFaxesAndEmailsPrimary)
                ->setContactType($faxContactType);

            $contactDetails->addPhone($fax);
        }

        $this->entityManager->persist($contactDetails);
        $this->entityManager->flush();

        return $contactDetails;
    }

    public function update(ContactDetail $businessContactDetails, array $data, $contactTypeName, $isPrimary = false)
    {
        $this->validator->validate($data);

        $this->addressService->persist($businessContactDetails->getAddress(), $data);

        /** @var Email $email */
        $email = ArrayUtils::firstOrNull(
            $businessContactDetails->getEmails(),
            function (Email $email) use ($contactTypeName, $isPrimary) {
                return
                    $email->getIsPrimary() === $isPrimary;
            }
        );

        if ($email) {
            $email->setEmail($data['email']);
        }

        /** @var Phone $phone */
        $phone = ArrayUtils::firstOrNull(
            $businessContactDetails->getPhones(),
            function (Phone $phone) use ($contactTypeName, $isPrimary) {
                return
                    $phone->getContactType()->getName() === $contactTypeName && $phone->getIsPrimary() === $isPrimary;
            }
        );

        if ($phone) {
            $phone->setNumber($data['phoneNumber']);
        }
        /** @var Phone $fax */
        $fax = ArrayUtils::firstOrNull(
            $businessContactDetails->getPhones(),
            function (Phone $fax) use ($isPrimary) {
                return $fax->getContactType()->getCode() === PhoneContactTypeCode::FAX
                       && $fax->getIsPrimary() === $isPrimary;
            }
        );

        if ($fax) {
            $fax->setNumber($data['faxNumber']);
        }

        $this->entityManager->persist($businessContactDetails);
        $this->entityManager->flush();

        return $businessContactDetails;
    }
}
