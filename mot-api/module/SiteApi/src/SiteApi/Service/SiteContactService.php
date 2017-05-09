<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\SiteContactPatchDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\SiteContact;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Repository\SiteContactRepository;
use DvsaEntities\Repository\SiteContactTypeRepository;

class SiteContactService extends AbstractService
{
    /** @var \DvsaCommonApi\Filter\XssFilter */
    protected $xssFilter;
    /** @var ContactDetailsService */
    private $contactDetailsService;
    /** @var UpdateVtsAssertion */
    private $updateVtsAssertion;
    /** @var SiteContactRepository */
    private $siteContactRepo;
    /** @var SiteContactTypeRepository */
    private $siteContactTypeRepo;

    public function __construct(
        EntityManager $entityManager,
        ContactDetailsService $contactDetailsService,
        XssFilter $xssFilter,
        UpdateVtsAssertion $updateVtsAssertion
    ) {
        parent::__construct($entityManager);

        $this->contactDetailsService = $contactDetailsService;
        $this->xssFilter = $xssFilter;
        $this->updateVtsAssertion = $updateVtsAssertion;

        $this->siteContactRepo = $entityManager->getRepository(SiteContact::class);
        $this->siteContactTypeRepo = $entityManager->getRepository(SiteContactType::class);
    }

    private function buildDto(array $data)
    {
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_ADDRESS, $data)) {
            $data[VehicleTestingStation::PATCH_PROPERTY_ADDRESS]['_class'] =
                'DvsaCommon\Dto\Contact\AddressDto';
        }

        $dto = DtoHydrator::jsonToDto(
            ['_class' => 'DvsaCommon\Dto\Site\SiteContactPatchDto'] + $data
        );

        return $dto;
    }

    public function patchContactFromJson($siteId, array $data)
    {
        $contact = $this->siteContactRepo->getHydratedByTypeCode($siteId, SiteContactTypeCode::BUSINESS);

        $dto = $this->buildDto($data);
        $dto = $this->xssFilter->filter($dto);

        $contactDetails = $contact->getDetails();
        $contactDetails = $this->contactDetailsService->patchContactDetailsFromDto(
            $this->mapSiteContactPatchDtoToContactDto($contactDetails, $siteId, $data, $dto), $contactDetails
        );

        $this->siteContactRepo->save($contactDetails);

        return ['id' => $contact->getId()];
    }

    private function mapSiteContactPatchDtoToContactDto(ContactDetail $contactDetails, $siteId, array $data, SiteContactPatchDto $siteContactPatchDto)
    {
        $contactDto = new ContactDto();
        $contactDto->setType($this->siteContactTypeRepo->getByCode(SiteContactTypeCode::BUSINESS));

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_PHONE, $data)) {
            $this->updateVtsAssertion->assertUpdatePhone($siteId);

            $newPhone = (new PhoneDto())
                ->setIsPrimary(true)
                ->setNumber($siteContactPatchDto->getPhone());

            if ($contactDetails->getPrimaryPhone()) {
                $newPhone->setContactType($contactDetails->getPrimaryPhone()->getContactType()->getCode());
            } else {
                $newPhone->setContactType(PhoneContactTypeCode::BUSINESS);
            }

            $contactDto->setPhones(
                [
                    $newPhone,
                ]
            );
        }

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_EMAIL, $data)) {
            $this->updateVtsAssertion->assertUpdateEmail($siteId);
            $contactDto->setEmails(
                [
                    (new EmailDto())
                        ->setIsPrimary(true)
                        ->setEmail($siteContactPatchDto->getEmail()),
                ]
            );
        }

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_ADDRESS, $data)) {
            $this->updateVtsAssertion->assertUpdateAddress($siteId);
            $contactDto->setAddress($siteContactPatchDto->getAddress());
        }

        return $contactDto;
    }
}
