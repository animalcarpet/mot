<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\SiteFacility;

/**
 * Class SiteFacilityRepository.
 *
 * @codeCoverageIgnore
 */
class SiteFacilityRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @return SiteFacility
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $facility = $this->find($id);
        if ($facility === null) {
            throw new NotFoundException('SiteFacility '.$id.' not found');
        }

        return $facility;
    }
}
