<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Service\DateMappingUtils;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;

/**
 * Class OrganisationPositionMapper.
 */
class OrganisationPositionMapper extends AbstractApiMapper
{
    private $personMapper;

    public function __construct(Hydrator $hydrator)
    {
        $this->personMapper = new PersonMapper();
    }

    /**
     * @param $positions OrganisationBusinessRoleMap[]
     *
     * @return OrganisationPositionDto[]
     */
    public function manyToDto($positions)
    {
        return parent::manyToDto($positions);
    }

    /**
     * @param $position OrganisationBusinessRoleMap
     *
     * @return OrganisationPositionDto
     */
    public function toDto($position)
    {
        $organisationPositionDto = new OrganisationPositionDto();

        $organisationPositionDto->setId($position->getId());
        $organisationPositionDto->setRole($position->getOrganisationBusinessRole()->getName());  //TODO VM-8254 8: replace with getCode()
        $organisationPositionDto->setPerson($this->personMapper->toDto($position->getPerson()));
        $organisationPositionDto->setStatus($position->getBusinessRoleStatus()->getCode());

        $actionedOn = $position->getValidFrom() ? $position->getValidFrom() : $position->getCreatedOn();
        $organisationPositionDto->setActionedOn(DateMappingUtils::extractDateTimeObject($actionedOn));

        return $organisationPositionDto;
    }
}
