<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\FacilityType;

/**
 * Class FacilityTypeRepository.
 *
 * @codeCoverageIgnore
 */
class FacilityTypeRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;

    /**
     * @param $id
     *
     * @return FacilityType
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $facilityType = $this->find($id);
        if ($facilityType === null) {
            throw new NotFoundException('FacilityType', $id);
        }

        return $facilityType;
    }
}
