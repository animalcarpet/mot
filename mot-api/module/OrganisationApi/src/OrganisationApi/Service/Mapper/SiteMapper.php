<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContact;

/**
 * Class SiteMapper
 *
 * @package OrganisationApi\Service\Mapper
 */
class SiteMapper extends AbstractApiMapper
{
    private $contactMapper;

    public function __construct()
    {
        $this->contactMapper = new ContactMapper();
    }

    /**
     * @param $sites Site[]
     *
     * @return VehicleTestingStationDto[]
     */
    public function manyToDto($sites)
    {
        return parent::manyToDto($sites);
    }

    /**
     * @param $site Site
     *
     * @return VehicleTestingStationDto
     */
    public function toDto($site)
    {
        $siteDto = new VehicleTestingStationDto();
        $siteDto->setId($site->getId());
        $siteDto->setName($site->getName());
        $siteDto->setSiteNumber($site->getSiteNumber());

        $contactDtos = $this->mapContacts($site->getContacts());
        $siteDto->setContacts($contactDtos);

        return $siteDto;
    }

    /**
     * @param $contacts SiteContact[]
     *
     * @return SiteContactDto[]
     */
    private function mapContacts($contacts)
    {
        $contactsDtos = [];

        foreach ($contacts as $contact) {
            /** @var SiteContactDto $contactDto */
            $contactDto = new SiteContactDto();
            $contactDto = $this->contactMapper->toDto($contact->getDetails(), $contactDto);
            $contactDto->setType($contact->getType()->getCode());
            $contactsDtos[] = $contactDto;
        }

        return $contactsDtos;
    }
}
