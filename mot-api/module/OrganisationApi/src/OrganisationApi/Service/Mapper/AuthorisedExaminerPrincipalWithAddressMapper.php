<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\AuthorisedExaminerPrincipal\AuthorisedExaminerPrincipalDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\AddressDto;

class AuthorisedExaminerPrincipalWithAddressMapper extends AbstractApiMapper
{
    private $aepMapper;

    public function __construct()
    {
        $this->aepMapper = new AuthorisedExaminerPrincipalMapper();
    }

    /**
     * @param $positions Person[]
     *
     * @return PersonDto[]
     */
    public function manyToDto($positions)
    {
        return parent::manyToDto($positions);
    }

    /**
     * @param AuthorisedExaminerPrincipal $aep
     *
     * @return AuthorisedExaminerPrincipalDto
     */
    public function toDto($aep)
    {
        $aepDto = $this->aepMapper->toDto($aep);
        $contactDetails = $aep->getContactDetails();
        $address = $contactDetails->getAddress();

        $addressDto = new AddressDto();
        $addressDto
            ->setAddressLine1($address->getAddressLine1())
            ->setAddressLine2($address->getAddressLine2())
            ->setAddressLine3($address->getAddressLine3())
            ->setPostcode($address->getPostcode())
            ->setTown($address->getTown())
            ->setCountry($address->getCountry())
            ;

        $contactDetails = new ContactDto();
        $contactDetails->setAddress($addressDto);

        $aepDto->setContactDetails($contactDetails);

        return $aepDto;
    }
}
