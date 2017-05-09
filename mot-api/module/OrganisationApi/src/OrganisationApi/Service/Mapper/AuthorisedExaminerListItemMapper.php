<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Common\AuthForAeStatusDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerListItemDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\OrganisationContact;

/**
 * Mapper class to display Auth Examiner in list (search).
 */
class AuthorisedExaminerListItemMapper extends AbstractApiMapper
{
    /** @var ContactMapper */
    private $contactMapper;

    public function __construct()
    {
        $this->contactMapper = new ContactMapper();

        parent::__construct();
    }

    /**
     * @param AuthorisationForAuthorisedExaminer $ae
     *
     * @return AuthorisedExaminerListItemDto
     */
    public function toDto($ae)
    {
        $org = $ae->getOrganisation();
        $status = $ae->getStatus();

        $statusDto = new AuthForAeStatusDto();
        $statusDto
            ->setCode($status->getCode())
            ->setName($status->getName());

        $organisationType = null;
        if ($org->getOrganisationType()) {
            $organisationType = $org->getOrganisationType()->getName();
        }

        $aeDto = new AuthorisedExaminerListItemDto();
        $aeDto
            ->setId($org->getId())
            ->setType($organisationType)
            ->setName($org->getName())
            ->setAuthorisedExaminerRef($ae->getNumber())
            ->setStatus($statusDto);

        //  --  map contact --
        $contact = $org->getCorrespondenceContact();
        if ($contact instanceof OrganisationContact) {
            $contactDto = $this->contactMapper->toDto(
                $contact->getDetails(),
                new ContactDto()
            );

            $aeDto->setAddress($contactDto->getAddress());
            $aeDto->setPhone($contactDto->getPrimaryPhoneNumber());
        }

        return $aeDto;
    }

    /**
     * @param $authorisedExaminers
     *
     * @return AuthorisedExaminerListItemDto[]
     */
    public function manyToDto($authorisedExaminers)
    {
        return parent::manyToDto($authorisedExaminers);
    }
}
