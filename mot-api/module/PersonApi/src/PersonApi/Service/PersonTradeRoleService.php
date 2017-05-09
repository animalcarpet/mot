<?php

namespace PersonApi\Service;

use DvsaCommon\ApiClient\Person\PersonTradeRoles\Dto\PersonTradeRoleDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Formatting\AddressFormatter;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;

class PersonTradeRoleService
{
    private $organisationPositionRepository;
    private $sitePositionRepository;

    public function __construct(
        OrganisationBusinessRoleMapRepository $organisationPositionRepository,
        SiteBusinessRoleMapRepository $sitePositionRepository
    ) {
        $this->organisationPositionRepository = $organisationPositionRepository;
        $this->sitePositionRepository = $sitePositionRepository;
    }

    public function getForPerson($personId)
    {
        $organisationPositions = $this->organisationPositionRepository->getActiveUserRoles($personId);
        $sitePositions = $this->sitePositionRepository->getActiveUserRoles($personId);

        $organisationDtos = ArrayUtils::map($organisationPositions, function (OrganisationBusinessRoleMap $position) {
            $contact = $position->getOrganisation()->getContactByType(OrganisationContactTypeCode::REGISTERED_COMPANY);
            $address = $contact ? $contact->getDetails()->getAddress() : null;
            $addressAsString = $address ? (new AddressFormatter())->format($address) : '';
            $ae = $position->getOrganisation()->getAuthorisedExaminer();
            $organisation = $position->getOrganisation();
            $organisationNumber = $organisation->getRegisteredCompanyNumber();
            $organisationNumber = !is_null($organisationNumber) ? $organisationNumber : $organisation->getName();

            $positionDto = new PersonTradeRoleDto();
            $positionDto->setPositionId($position->getId())
                ->setWorkplaceId($position->getOrganisation()->getId())
                ->setWorkplaceName($position->getOrganisation()->getName())
                ->setRoleCode($position->getOrganisationBusinessRole()->getCode())
                ->setAeId(!is_null($ae) ? $ae->getNumber() : $organisationNumber)
                ->setNumber($position->getOrganisation()->getAuthorisedExaminer()->getNumber())
                ->setAddress($addressAsString);

            return $positionDto;
        });

        $siteDtos = ArrayUtils::map($sitePositions, function (SiteBusinessRoleMap $position) {
            $contact = $position->getSite()->getContactByType(SiteContactTypeCode::BUSINESS);
            $address = $contact ? $contact->getDetails()->getAddress() : null;
            $addressAsString = $address ? (new AddressFormatter())->format($address) : '';
            $ae = $position->getSite()->getAuthorisedExaminer();
            $organisation = $position->getSite()->getOrganisation();
            $organisationNumber = $organisation->getRegisteredCompanyNumber();
            $organisationNumber = !is_null($organisationNumber) ? $organisationNumber : $organisation->getName();

            $positionDto = new PersonTradeRoleDto();
            $positionDto->setPositionId($position->getId())
                ->setWorkplaceId($position->getSite()->getId())
                ->setWorkplaceName($position->getSite()->getName())
                ->setRoleCode($position->getSiteBusinessRole()->getCode())
                ->setAeId(!is_null($ae) ? $ae->getNumber() : $organisationNumber)
                ->setNumber($position->getSite()->getSiteNumber())
                ->setAddress($addressAsString);

            return $positionDto;
        });

        $dtos = array_merge($organisationDtos, $siteDtos);

        return $dtos;
    }
}
